<?php

namespace App\Http\Controllers\Agente;

use App\Enums\EstatusConversacion;
use App\Http\Controllers\Controller;
use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Models\Obra;
use App\Models\Proveedor;
use App\Services\Agente\AgenteCompras;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class ConversacionController extends Controller
{
    /**
     * Pantalla del agente: conversaciones, chat y borrador de la orden.
     */
    public function index(Request $request, AgenteCompras $agente): Response
    {
        $conversaciones = $request->user()->conversaciones()
            ->with('obra')
            ->orderByDesc('ultimo_mensaje_at')
            ->orderByDesc('id')
            ->get();

        $activa = $this->conversacionActiva($request, $conversaciones);

        return Inertia::render('agente/Index', [
            'conversaciones' => $conversaciones
                ->map(fn (Conversacion $conversacion): array => [
                    'id' => $conversacion->id,
                    'titulo' => $conversacion->titulo,
                    'obra' => $conversacion->obra?->nombre,
                    'ultimo_mensaje_at' => $conversacion->ultimo_mensaje_at?->diffForHumans(),
                ])
                ->all(),
            'conversacionActiva' => $activa?->id,
            'mensajes' => $activa?->mensajes()
                ->orderBy('id')
                ->get()
                ->map(fn (Mensaje $mensaje): array => $mensaje->paraFrontend())
                ->all() ?? [],
            'borrador' => $activa instanceof Conversacion ? $agente->estadoDelBorrador($activa) : null,
            'obras' => Obra::query()
                ->orderBy('codigo')
                ->get()
                ->map(fn (Obra $obra): array => [
                    'id' => $obra->id,
                    'codigo' => $obra->codigo,
                    'nombre' => $obra->nombre,
                    'cliente' => $obra->cliente,
                    'ubicacion' => $obra->ubicacion(),
                ])
                ->all(),
            'proveedores' => Proveedor::query()
                ->where('activo', true)
                ->orderBy('nombre')
                ->get()
                ->map(fn (Proveedor $proveedor): array => [
                    'id' => $proveedor->id,
                    'nombre' => $proveedor->nombre,
                    'ciudad' => $proveedor->ciudad,
                    'condicion_pago' => $proveedor->condicionDePago(),
                ])
                ->all(),
        ]);
    }

    /**
     * Abre una conversación nueva con el agente.
     */
    public function store(Request $request): RedirectResponse
    {
        $conversacion = $request->user()->conversaciones()->create([
            'titulo' => Conversacion::TITULO_INICIAL,
            'estatus' => EstatusConversacion::Abierta,
        ]);

        return to_route('agente.index', ['conversacion' => $conversacion->id]);
    }

    /**
     * Elimina una conversación con sus mensajes.
     */
    public function destroy(Conversacion $conversacion): RedirectResponse
    {
        $titulo = $conversacion->titulo;

        $conversacion->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Conversación «{$titulo}» eliminada.",
        ]);

        return to_route('agente.index');
    }

    /**
     * Conversación solicitada, o la más reciente del usuario.
     *
     * @param  Collection<int, Conversacion>  $conversaciones
     */
    private function conversacionActiva(Request $request, Collection $conversaciones): ?Conversacion
    {
        return $conversaciones->firstWhere('id', $request->integer('conversacion')) ?? $conversaciones->first();
    }
}
