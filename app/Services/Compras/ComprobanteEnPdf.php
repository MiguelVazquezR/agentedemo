<?php

namespace App\Services\Compras;

use App\Models\OrdenCompra;
use Barryvdh\DomPDF\Facade\Pdf as FachadaPdf;
use Barryvdh\DomPDF\PDF as DocumentoPdf;
use Illuminate\Http\Response;

/**
 * Arma el PDF de una orden de compra a partir de la vista imprimible.
 */
class ComprobanteEnPdf
{
    /**
     * PDF de la orden como texto binario (para adjuntarlo al correo).
     */
    public function contenido(OrdenCompra $orden): string
    {
        return $this->pdf($orden)->output();
    }

    /**
     * Descarga del PDF con el folio de la orden como nombre de archivo.
     */
    public function descargar(OrdenCompra $orden): Response
    {
        return $this->pdf($orden)->download($this->nombreArchivo($orden));
    }

    /**
     * Nombre del archivo que se descarga o se adjunta.
     */
    public function nombreArchivo(OrdenCompra $orden): string
    {
        return 'orden-compra-'.($orden->folio ?? $orden->id).'.pdf';
    }

    private function pdf(OrdenCompra $orden): DocumentoPdf
    {
        $orden->loadMissing(['obra', 'proveedor', 'items.material']);

        return FachadaPdf::loadView('ordenes.pdf', [
            'orden' => $orden,
            'empresa' => config('compras.empresa'),
            'moneda' => (string) config('compras.moneda'),
        ])->setPaper('letter');
    }
}
