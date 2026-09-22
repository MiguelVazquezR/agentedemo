<?php

namespace App\Http\Controllers\Ordenes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ordenes\EnviarOrdenRequest;
use App\Models\OrdenCompra;
use App\Services\Compras\EnviadorDeOrdenes;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class EnvioDeOrdenController extends Controller
{
    /**
     * Manda la orden al proveedor por correo con el PDF adjunto.
     */
    public function store(EnviarOrdenRequest $request, OrdenCompra $orden, EnviadorDeOrdenes $enviador): RedirectResponse
    {
        $envio = $enviador->enviar(
            $orden,
            $request->user(),
            $request->comentario(),
            $request->copias(),
        );

        Inertia::flash('toast', $envio->exito
            ? ['type' => 'success', 'message' => "Orden {$orden->folio} enviada a {$envio->destinatario}."]
            : ['type' => 'error', 'message' => 'No se pudo enviar la orden: '.$envio->error]);

        return back();
    }
}
