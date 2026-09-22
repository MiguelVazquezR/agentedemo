<?php

namespace App\Http\Controllers;

use App\Enums\EstatusObra;
use App\Enums\EstatusOrdenCompra;
use App\Models\Material;
use App\Models\Obra;
use App\Models\OrdenCompra;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Resumen de la operación: órdenes, obras y catálogo.
     */
    public function __invoke(Request $request): Response
    {
        $generadas = OrdenCompra::query()->generadas();

        return Inertia::render('Dashboard', [
            'resumen' => [
                'ordenes' => (clone $generadas)->count(),
                'monto' => (float) (clone $generadas)->sum('total'),
                'por_enviar' => (clone $generadas)->where('estatus', EstatusOrdenCompra::Generada)->count(),
                'materiales' => Material::query()->where('activo', true)->count(),
                'obras' => Obra::query()->where('estatus', EstatusObra::EnEjecucion)->count(),
                'conversaciones' => $request->user()?->conversaciones()->count() ?? 0,
            ],
            'ordenesRecientes' => OrdenCompra::query()
                ->generadas()
                ->with(['obra:id,codigo,nombre', 'proveedor:id,nombre', 'items:id,orden_compra_id'])
                ->orderByDesc('generada_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get()
                ->map(fn (OrdenCompra $orden): array => $orden->paraLista())
                ->all(),
            'obras' => Obra::query()
                ->withCount('ordenesGeneradas')
                ->withSum('ordenesGeneradas', 'total')
                ->orderBy('codigo')
                ->get()
                ->map(fn (Obra $obra): array => [
                    'id' => $obra->id,
                    'codigo' => $obra->codigo,
                    'nombre' => $obra->nombre,
                    'cliente' => $obra->cliente,
                    'ubicacion' => $obra->ubicacion(),
                    'estatus' => $obra->estatus->value,
                    'estatus_etiqueta' => $obra->estatus->label(),
                    'estatus_clase' => $obra->estatus->badgeClass(),
                    'presupuesto' => (float) $obra->presupuesto_autorizado,
                    'ordenes' => $obra->ordenes_generadas_count,
                    'comprometido' => (float) ($obra->ordenes_generadas_sum_total ?? 0),
                ])
                ->all(),
        ]);
    }
}
