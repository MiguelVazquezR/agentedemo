<x-mail::message>
# Orden de compra {{ $orden->folio }}

Hola{{ $orden->proveedor?->contacto ? ' '.$orden->proveedor->contacto : '' }}:

Adjuntamos la orden de compra para **{{ $orden->obra?->nombre ?? 'la obra solicitada' }}**.
Te pedimos confirmar existencias, precio y fecha de entrega.

@if ($comentario)
> {{ $comentario }}
@endif

<x-mail::table>
| Material | Cantidad | Importe |
|:---------|:--------:|--------:|
@foreach ($orden->items as $item)
| {{ $item->material->descripcionCompleta() }} | {{ rtrim(rtrim(number_format((float) $item->cantidad, 2, '.', ','), '0'), '.') }} {{ $item->material->unidad->simbolo() }} | ${{ number_format((float) $item->importe, 2) }} |
@endforeach
| **Total {{ $moneda }}** |  | **${{ number_format((float) $orden->total, 2) }}** |
</x-mail::table>

<x-mail::panel>
@if ($orden->fecha_requerida)
**Fecha requerida en obra:** {{ $orden->fecha_requerida->format('d/m/Y') }}
@endif

@if ($orden->condiciones_pago)
**Condiciones de pago:** {{ $orden->condiciones_pago }}
@endif

**Partidas:** {{ $orden->items->count() }} · **Subtotal:** ${{ number_format((float) $orden->subtotal, 2) }} · **IVA:** ${{ number_format((float) $orden->iva, 2) }}
</x-mail::panel>

El detalle completo de materiales, precios unitarios e importes va en el PDF adjunto.

Gracias por su atención,

{{ $empresa['nombre'] ?? '' }}
{{ $empresa['email'] ?? '' }} · {{ $empresa['telefono'] ?? '' }}
</x-mail::message>
