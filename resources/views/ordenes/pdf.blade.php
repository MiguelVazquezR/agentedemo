<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Orden de compra {{ $orden->folio }}</title>
    <style>
        @page { margin: 26px 30px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #1f2937; }
        h1 { font-size: 15px; margin: 0 0 4px 0; }
        table { border-collapse: collapse; }
        .encabezado { width: 100%; border-bottom: 2px solid #1f2937; padding-bottom: 8px; margin-bottom: 14px; }
        .empresa { font-size: 8.5px; color: #4b5563; line-height: 1.6; }
        .folio { text-align: right; vertical-align: top; width: 38%; font-size: 12px; font-weight: bold; }
        .folio small { display: block; font-weight: normal; font-size: 8.5px; color: #4b5563; margin-top: 4px; line-height: 1.6; }
        .tarjetas { width: 100%; margin-bottom: 14px; }
        .tarjeta { width: 48%; vertical-align: top; padding-right: 12px; line-height: 1.6; }
        .titulo { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #6b7280; margin-bottom: 3px; }
        table.partidas { width: 100%; }
        table.partidas th { background: #f3f4f6; font-size: 8px; text-transform: uppercase; letter-spacing: 0.4px; color: #374151; text-align: left; padding: 5px 6px; border-bottom: 1px solid #d1d5db; }
        table.partidas td { padding: 5px 6px; border-bottom: 1px solid #e5e7eb; line-height: 1.5; }
        .derecha { text-align: right; }
        .totales { width: 40%; margin-left: 60%; margin-top: 8px; }
        .totales td { padding: 3px 6px; }
        .total-final td { border-top: 1px solid #9ca3af; font-weight: bold; font-size: 11px; padding-top: 5px; }
        .pie { margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 6px; font-size: 8px; color: #6b7280; line-height: 1.6; }
    </style>
</head>
<body>
    <table class="encabezado">
        <tr>
            <td>
                <h1>{{ $empresa['nombre'] ?? 'Constructora Demo México' }}</h1>
                <div class="empresa">
                    RFC {{ $empresa['rfc'] ?? '' }}<br>
                    {{ $empresa['direccion'] ?? '' }}<br>
                    {{ $empresa['ciudad'] ?? '' }} · Tel. {{ $empresa['telefono'] ?? '' }}<br>
                    {{ $empresa['email'] ?? '' }}
                </div>
            </td>
            <td class="folio">
                ORDEN DE COMPRA<br>
                {{ $orden->folio ?? 'Sin folio' }}
                <small>
                    Estatus: {{ $orden->estatus->label() }}<br>
                    Generada: {{ $orden->generada_at?->format('d/m/Y H:i') ?? '—' }}<br>
                    Partidas: {{ $orden->items->count() }}
                </small>
            </td>
        </tr>
    </table>

    <table class="tarjetas">
        <tr>
            <td class="tarjeta">
                <div class="titulo">Obra / destino</div>
                <strong>{{ $orden->obra?->nombre ?? 'Sin obra asignada' }}</strong>
                @if ($orden->obra)
                    <div>{{ $orden->obra->codigo }} · {{ $orden->obra->cliente }}</div>
                    <div>{{ $orden->obra->ubicacion() }}</div>
                @else
                    <div>Por asignar</div>
                @endif
                <div>Fecha requerida: {{ $orden->fecha_requerida?->format('d/m/Y') ?? 'Por definir' }}</div>
            </td>
            <td class="tarjeta">
                <div class="titulo">Proveedor</div>
                <strong>{{ $orden->proveedor?->nombre ?? 'Sin proveedor asignado' }}</strong>
                @if ($orden->proveedor)
                    <div>{{ $orden->proveedor->contacto }}</div>
                    <div>{{ $orden->proveedor->email }}</div>
                    <div>{{ $orden->proveedor->ciudad }}</div>
                @else
                    <div>Por asignar</div>
                @endif
                <div>Condiciones de pago: {{ $orden->condiciones_pago ?? $orden->proveedor?->condicionDePago() ?? 'Por definir' }}</div>
            </td>
        </tr>
    </table>

    <table class="partidas">
        <thead>
            <tr>
                <th style="width: 13%">SKU</th>
                <th style="width: 45%">Descripción</th>
                <th class="derecha" style="width: 10%">Cantidad</th>
                <th style="width: 8%">Unidad</th>
                <th class="derecha" style="width: 12%">P. unitario</th>
                <th class="derecha" style="width: 12%">Importe</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orden->items as $item)
                <tr>
                    <td>{{ $item->material->sku }}</td>
                    <td>{{ $item->material->descripcionCompleta() }}</td>
                    <td class="derecha">{{ rtrim(rtrim(number_format((float) $item->cantidad, 2, '.', ','), '0'), '.') }}</td>
                    <td>{{ $item->material->unidad->simbolo() }}</td>
                    <td class="derecha">{{ '$'.number_format((float) $item->precio_unitario, 2) }}</td>
                    <td class="derecha">{{ '$'.number_format((float) $item->importe, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="derecha">Esta orden todavía no tiene partidas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td>Subtotal</td>
            <td class="derecha">{{ '$'.number_format((float) $orden->subtotal, 2) }}</td>
        </tr>
        @if ((float) $orden->descuento > 0)
            <tr>
                <td>Descuento</td>
                <td class="derecha">- {{ '$'.number_format((float) $orden->descuento, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td>IVA 16%</td>
            <td class="derecha">{{ '$'.number_format((float) $orden->iva, 2) }}</td>
        </tr>
        <tr class="total-final">
            <td>Total {{ $moneda }}</td>
            <td class="derecha">{{ '$'.number_format((float) $orden->total, 2) }}</td>
        </tr>
    </table>

    @if ($orden->observaciones)
        <div class="titulo" style="margin-top: 14px">Observaciones</div>
        <div>{{ $orden->observaciones }}</div>
    @endif

    <div class="pie">
        Documento generado por el agente de compras de {{ $empresa['nombre'] ?? '' }}
        el {{ $orden->generada_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}.<br>
        Cualquier aclaración sobre esta orden: {{ $empresa['email'] ?? '' }} · {{ $empresa['telefono'] ?? '' }}.
    </div>
</body>
</html>
