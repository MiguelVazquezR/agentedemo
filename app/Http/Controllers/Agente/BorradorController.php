<?php

namespace App\Http\Controllers\Agente;

use App\Enums\EstatusOrdenCompra;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agente\DestroyPartidaRequest;
use App\Http\Requests\Agente\StorePartidaRequest;
use App\Http\Requests\Agente\UpdateBorradorRequest;
use App\Http\Requests\Agente\UpdatePartidaRequest;
use App\Models\Conversacion;
use App\Models\Material;
use App\Models\Proveedor;
use App\Services\Agente\AgenteCompras;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class BorradorController extends Controller
{
    /**
     * Ajusta la obra, el proveedor o la fecha requerida de la lista.
     */
    public function update(UpdateBorradorRequest $request, Conversacion $conversacion, AgenteCompras $agente): RedirectResponse
    {
        $borrador = $agente->borradorDe($conversacion);
        $datos = $request->validated();

        if (array_key_exists('proveedor_id', $datos) && $datos['proveedor_id'] !== null) {
            $datos['condiciones_pago'] = Proveedor::query()
                ->whereKey($datos['proveedor_id'])
                ->firstOrFail()
                ->condicionDePago();
        }

        $borrador->update($datos);

        $borrador->update([
            'estatus' => $borrador->estaCompleta() ? EstatusOrdenCompra::Completa : EstatusOrdenCompra::Borrador,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Lista actualizada.']);

        return back();
    }

    /**
     * Agrega una partida del catálogo a la lista.
     */
    public function store(StorePartidaRequest $request, Conversacion $conversacion, AgenteCompras $agente): RedirectResponse
    {
        $material = Material::query()->where('activo', true)->findOrFail((int) $request->integer('material_id'));

        $agente->borradorDe($conversacion)->agregarMaterial($material, (float) $request->float('cantidad'));
        $this->actualizarEstatus($conversacion, $agente);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Material agregado a la lista.']);

        return back();
    }

    /**
     * Corrige la cantidad de una partida.
     */
    public function updatePartida(UpdatePartidaRequest $request, Conversacion $conversacion, AgenteCompras $agente): RedirectResponse
    {
        $agregado = $agente->borradorDe($conversacion)
            ->actualizarCantidad((int) $request->integer('material_id'), (float) $request->float('cantidad'));

        $this->actualizarEstatus($conversacion, $agente);

        Inertia::flash($agregado
            ? ['toast' => ['type' => 'success', 'message' => 'Cantidad actualizada.']]
            : ['toast' => ['type' => 'warning', 'message' => 'Ese material ya no está en la lista.']]);

        return back();
    }

    /**
     * Quita una partida de la lista.
     */
    public function destroyPartida(DestroyPartidaRequest $request, Conversacion $conversacion, AgenteCompras $agente): RedirectResponse
    {
        $quitado = $agente->borradorDe($conversacion)->quitarMaterial((int) $request->integer('material_id'));

        $this->actualizarEstatus($conversacion, $agente);

        Inertia::flash($quitado
            ? ['toast' => ['type' => 'success', 'message' => 'Material quitado de la lista.']]
            : ['toast' => ['type' => 'warning', 'message' => 'Ese material ya no está en la lista.']]);

        return back();
    }

    /**
     * Mantiene el estatus del borrador a la par de su contenido.
     */
    private function actualizarEstatus(Conversacion $conversacion, AgenteCompras $agente): void
    {
        $borrador = $agente->borradorDe($conversacion);

        $borrador->update([
            'estatus' => $borrador->estaCompleta() ? EstatusOrdenCompra::Completa : EstatusOrdenCompra::Borrador,
        ]);
    }
}
