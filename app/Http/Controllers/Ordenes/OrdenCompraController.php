<?php

namespace App\Http\Controllers\Ordenes;

use App\Enums\EstatusOrdenCompra;
use App\Http\Controllers\Controller;
use App\Models\Conversacion;
use App\Models\OrdenCompra;
use App\Services\Agente\AgenteCompras;
use App\Services\Compras\ComprobanteEnPdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrdenCompraController extends Controller
{
    /**
     * Historial de órdenes de compra ya generadas.
     */
    public function index(): Response
    {
        $ordenes = OrdenCompra::query()
            ->generadas()
            ->with(['obra:id,codigo,nombre', 'proveedor:id,nombre', 'items:id,orden_compra_id'])
            ->orderByDesc('generada_at')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('ordenes/Index', [
            'ordenes' => $ordenes
                ->map(fn (OrdenCompra $orden): array => $orden->paraLista())
                ->all(),
            'resumen' => [
                'cantidad' => $ordenes->count(),
                'monto' => (float) $ordenes->sum(fn (OrdenCompra $orden): float => (float) $orden->total),
                'por_enviar' => $ordenes
                    ->filter(fn (OrdenCompra $orden): bool => $orden->estatus === EstatusOrdenCompra::Generada)
                    ->count(),
            ],
        ]);
    }

    /**
     * Detalle de una orden con partidas, totales y envíos.
     */
    public function show(OrdenCompra $orden): Response
    {
        $orden->load(['obra', 'proveedor', 'items.material', 'envios.user']);

        return Inertia::render('ordenes/Show', [
            'orden' => $orden->paraDetalle(),
        ]);
    }

    /**
     * Convierte la lista de la conversación en orden de compra con folio.
     */
    public function store(Conversacion $conversacion, AgenteCompras $agente): RedirectResponse
    {
        $borrador = $agente->borradorDe($conversacion);

        if (! $borrador->puedeGenerarse()) {
            Inertia::flash('toast', [
                'type' => 'warning',
                'message' => 'Completa la obra, el proveedor y los materiales antes de generar la orden.',
            ]);

            return back();
        }

        $borrador->generar();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Orden {$borrador->folio} generada correctamente.",
        ]);

        return to_route('ordenes.show', ['orden' => $borrador->id]);
    }

    /**
     * PDF de la orden listo para imprimir o enviar.
     */
    public function pdf(OrdenCompra $orden, ComprobanteEnPdf $comprobante): HttpResponse
    {
        return $comprobante->descargar($orden);
    }
}
