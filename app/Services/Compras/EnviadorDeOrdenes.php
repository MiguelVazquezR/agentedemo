<?php

namespace App\Services\Compras;

use App\Mail\OrdenCompraEnviada;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraEnvio;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Envía la orden de compra al proveedor por correo y deja registro del intento.
 */
class EnviadorDeOrdenes
{
    public function __construct(private readonly ComprobanteEnPdf $comprobante) {}

    /**
     * Envía la orden y devuelve el registro del envío (con o sin éxito).
     *
     * @param  array<int, string>  $copias
     */
    public function enviar(
        OrdenCompra $orden,
        ?User $usuario = null,
        ?string $comentario = null,
        array $copias = [],
    ): OrdenCompraEnvio {
        $destinatario = (string) $orden->proveedor?->email;
        $archivo = $this->comprobante->nombreArchivo($orden);

        $envio = $orden->envios()->create([
            'user_id' => $usuario?->id,
            'destinatario' => $destinatario,
            'cc' => implode(', ', $copias),
            'asunto' => $this->asunto($orden),
            'mensaje' => $comentario,
            'pdf_archivo' => $archivo,
        ]);

        if ($destinatario === '') {
            $envio->update(['error' => 'El proveedor no tiene correo registrado.']);

            return $envio;
        }

        try {
            Mail::to($destinatario)
                ->cc($copias)
                ->send(new OrdenCompraEnviada(
                    $orden,
                    $comentario,
                    $this->comprobante->contenido($orden),
                    $archivo,
                ));

            $envio->update(['exito' => true, 'enviado_at' => now()]);
            $orden->marcarEnviada();
        } catch (Throwable $excepcion) {
            report($excepcion);

            $envio->update(['error' => $excepcion->getMessage()]);
        }

        return $envio;
    }

    /**
     * Asunto del correo que recibe el proveedor.
     */
    private function asunto(OrdenCompra $orden): string
    {
        $obra = $orden->obra?->nombre;

        return "Orden de compra {$orden->folio}".($obra === null ? '' : " · {$obra}");
    }
}
