<?php

namespace App\Http\Controllers\Agente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agente\StoreMensajeRequest;
use App\Models\Conversacion;
use App\Services\Agente\AgenteCompras;
use App\Services\DeepSeek\DeepSeekException;
use Illuminate\Http\JsonResponse;

class MensajeController extends Controller
{
    /**
     * Envía un mensaje al agente y devuelve su respuesta con el borrador.
     *
     * Se consume con useHttp (petición HTTP independiente de Inertia).
     */
    public function store(StoreMensajeRequest $request, Conversacion $conversacion, AgenteCompras $agente): JsonResponse
    {
        try {
            $mensaje = $agente->responder($conversacion, (string) $request->string('mensaje')->trim());
        } catch (DeepSeekException $excepcion) {
            report($excepcion);

            return response()->json(['message' => $excepcion->getMessage()], 503);
        }

        return response()->json([
            'mensaje' => $mensaje->paraFrontend(),
            'borrador' => $agente->estadoDelBorrador($conversacion->refresh()),
            'titulo' => $conversacion->titulo,
        ]);
    }
}
