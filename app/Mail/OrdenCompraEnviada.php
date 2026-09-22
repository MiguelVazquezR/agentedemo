<?php

namespace App\Mail;

use App\Models\OrdenCompra;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Orden de compra que se envía al proveedor con el PDF adjunto.
 */
class OrdenCompraEnviada extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public OrdenCompra $orden,
        public ?string $comentario = null,
        public ?string $pdf = null,
        public ?string $archivo = null,
    ) {}

    /**
     * Asunto del correo.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Orden de compra {$this->orden->folio} · ".(string) config('compras.empresa.nombre'),
        );
    }

    /**
     * Cuerpo del correo.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.orden-compra-enviada',
            with: [
                'orden' => $this->orden,
                'empresa' => config('compras.empresa'),
                'moneda' => (string) config('compras.moneda'),
                'comentario' => $this->comentario,
            ],
        );
    }

    /**
     * PDF de la orden adjunto.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if ($this->pdf === null || $this->archivo === null) {
            return [];
        }

        return [
            Attachment::fromData(fn (): string => (string) $this->pdf, $this->archivo)
                ->withMime('application/pdf'),
        ];
    }
}
