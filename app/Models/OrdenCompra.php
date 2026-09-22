<?php

namespace App\Models;

use App\Enums\EstatusOrdenCompra;
use Database\Factories\OrdenCompraFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * @property int $id
 * @property string|null $folio
 * @property EstatusOrdenCompra $estatus
 * @property int|null $conversacion_id
 * @property int|null $obra_id
 * @property int|null $proveedor_id
 * @property int|null $user_id
 * @property string|float $subtotal
 * @property string|float $descuento
 * @property string|float $iva
 * @property string|float $total
 * @property string|null $condiciones_pago
 * @property Carbon|null $fecha_requerida
 * @property string|null $observaciones
 * @property Carbon|null $generada_at
 * @property Carbon|null $enviada_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Obra|null $obra
 * @property-read Proveedor|null $proveedor
 * @property-read Conversacion|null $conversacion
 * @property-read User|null $user
 */
#[Table('ordenes_compra')]
#[Fillable([
    'folio',
    'estatus',
    'conversacion_id',
    'obra_id',
    'proveedor_id',
    'user_id',
    'subtotal',
    'descuento',
    'iva',
    'total',
    'condiciones_pago',
    'fecha_requerida',
    'observaciones',
    'generada_at',
    'enviada_at',
])]
class OrdenCompra extends Model
{
    /** @use HasFactory<OrdenCompraFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estatus' => EstatusOrdenCompra::class,
            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'iva' => 'decimal:2',
            'total' => 'decimal:2',
            'fecha_requerida' => 'date',
            'generada_at' => 'datetime',
            'enviada_at' => 'datetime',
        ];
    }

    /**
     * Conversación con el agente que dio origen a la orden.
     *
     * @return BelongsTo<Conversacion, $this>
     */
    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(Conversacion::class);
    }

    /**
     * Obra donde se solicita el material.
     *
     * @return BelongsTo<Obra, $this>
     */
    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    /**
     * Proveedor al que se enviará la orden.
     *
     * @return BelongsTo<Proveedor, $this>
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    /**
     * Usuario que armó la lista.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Partidas de la lista de materiales.
     *
     * @return HasMany<OrdenCompraItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrdenCompraItem::class)->orderBy('orden')->orderBy('id');
    }

    /**
     * Correos enviados al proveedor.
     *
     * @return HasMany<OrdenCompraEnvio, $this>
     */
    public function envios(): HasMany
    {
        return $this->hasMany(OrdenCompraEnvio::class)->latest('id');
    }

    /**
     * Agrega (o suma cantidad a) un material del catálogo y recalcula totales.
     */
    public function agregarMaterial(Material $material, float $cantidad, ?string $notas = null): OrdenCompraItem
    {
        $item = $this->items()->firstOrNew(['material_id' => $material->id]);
        $item->cantidad = $item->exists ? (float) $item->cantidad + $cantidad : $cantidad;
        $item->precio_unitario = $material->precio_unitario;
        $item->orden = $item->exists ? (int) $item->orden : (int) $this->items()->max('orden') + 1;

        if ($notas !== null) {
            $item->notas = $notas;
        }

        $item->save();

        return $item;
    }

    /**
     * Fija la cantidad exacta de una partida existente.
     */
    public function actualizarCantidad(int $materialId, float $cantidad): bool
    {
        $item = $this->items()->where('material_id', $materialId)->first();

        if ($item === null) {
            return false;
        }

        $item->cantidad = $cantidad;
        $item->save();

        return true;
    }

    /**
     * Quita un material de la lista.
     */
    public function quitarMaterial(int $materialId): bool
    {
        $item = $this->items()->where('material_id', $materialId)->first();

        if ($item === null) {
            return false;
        }

        $item->delete();

        return true;
    }

    /**
     * Recalcula subtotal, IVA y total a partir de las partidas.
     */
    public function recalcularTotales(): void
    {
        $subtotal = round((float) $this->items()->sum('importe'), 2);
        $base = max(round($subtotal - (float) $this->descuento, 2), 0);
        $iva = round($base * (float) config('compras.iva'), 2);

        $this->forceFill([
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => round($base + $iva, 2),
        ])->save();
    }

    /**
     * Requisitos que todavía faltan para poder generar la orden.
     *
     * @return array<int, string>
     */
    public function pendientes(): array
    {
        $pendientes = [];

        if ($this->obra_id === null) {
            $pendientes[] = 'Selecciona la obra donde se solicita el material';
        }

        if ($this->proveedor_id === null) {
            $pendientes[] = 'Selecciona el proveedor al que se enviará la orden';
        }

        if ($this->items()->doesntExist()) {
            $pendientes[] = 'Agrega al menos un material a la lista';
        }

        return $pendientes;
    }

    /**
     * La lista tiene todo lo necesario para convertirse en orden de compra.
     */
    public function estaCompleta(): bool
    {
        return $this->pendientes() === [];
    }

    /**
     * Puede generarse el folio definitivo de la orden.
     */
    public function puedeGenerarse(): bool
    {
        return $this->estaCompleta() && $this->esBorrador();
    }

    /**
     * Es todavía una orden en construcción.
     */
    public function esBorrador(): bool
    {
        return in_array($this->estatus, [EstatusOrdenCompra::Borrador, EstatusOrdenCompra::Completa], true);
    }

    /**
     * Scope: solo órdenes ya generadas (excluye borradores).
     *
     * @param  Builder<OrdenCompra>  $query
     */
    public function scopeGeneradas(Builder $query): void
    {
        $query->whereNotIn('estatus', [EstatusOrdenCompra::Borrador, EstatusOrdenCompra::Completa]);
    }

    /**
     * Asigna el folio definitivo y marca la orden como generada.
     */
    public function generar(): void
    {
        if (! $this->puedeGenerarse()) {
            throw new RuntimeException('La lista todavía no está completa para generar la orden.');
        }

        $this->forceFill([
            'folio' => $this->folio ?? static::siguienteFolio(),
            'estatus' => EstatusOrdenCompra::Generada,
            'generada_at' => now(),
        ])->save();
    }

    /**
     * Marca la orden como enviada al proveedor.
     */
    public function marcarEnviada(): void
    {
        $this->forceFill([
            'estatus' => EstatusOrdenCompra::Enviada,
            'enviada_at' => now(),
        ])->save();
    }

    /**
     * Siguiente folio del año en curso, por ejemplo OC-2026-0004.
     */
    public static function siguienteFolio(?int $anio = null): string
    {
        $prefijo = (string) config('compras.folio.prefijo');
        $digitos = (int) config('compras.folio.digitos');
        $anio ??= (int) now()->year;

        $ultimo = static::query()
            ->where('folio', 'like', "{$prefijo}-{$anio}-%")
            ->orderByDesc('folio')
            ->value('folio');

        $consecutivo = $ultimo === null ? 1 : ((int) substr((string) $ultimo, -$digitos)) + 1;

        return sprintf(
            '%s-%d-%s',
            $prefijo,
            $anio,
            str_pad((string) $consecutivo, $digitos, '0', STR_PAD_LEFT),
        );
    }

    /**
     * Datos de la orden para el listado del historial.
     *
     * @return array<string, mixed>
     */
    public function paraLista(): array
    {
        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'estatus' => $this->estatus->value,
            'estatus_etiqueta' => $this->estatus->label(),
            'estatus_clase' => $this->estatus->badgeClass(),
            'obra' => $this->obra?->nombre,
            'obra_codigo' => $this->obra?->codigo,
            'proveedor' => $this->proveedor?->nombre,
            'fecha_requerida' => $this->fecha_requerida?->toDateString(),
            'partidas' => $this->items->count(),
            'total' => (float) $this->total,
            'generada_en' => $this->generada_at?->toIso8601String(),
            'enviada_en' => $this->enviada_at?->toIso8601String(),
        ];
    }

    /**
     * Datos completos de la orden para la pantalla de detalle.
     *
     * @return array<string, mixed>
     */
    public function paraDetalle(): array
    {
        return [
            ...$this->paraLista(),
            'total_partidas' => $this->items->count(),
            'obra_id' => $this->obra_id,
            'obra_cliente' => $this->obra?->cliente,
            'obra_ubicacion' => $this->obra?->ubicacion(),
            'proveedor_id' => $this->proveedor_id,
            'proveedor_contacto' => $this->proveedor?->contacto,
            'proveedor_email' => $this->proveedor?->email,
            'proveedor_ciudad' => $this->proveedor?->ciudad,
            'conversacion_id' => $this->conversacion_id,
            'condiciones_pago' => $this->condiciones_pago,
            'observaciones' => $this->observaciones,
            'descuento' => (float) $this->descuento,
            'creada_en' => $this->created_at?->toIso8601String(),
            'partidas' => $this->items
                ->map(fn (OrdenCompraItem $item): array => $item->paraFrontend())
                ->all(),
            'envios' => $this->envios
                ->map(fn (OrdenCompraEnvio $envio): array => $envio->paraFrontend())
                ->all(),
            'puede_enviarse' => $this->proveedor?->email !== null && $this->estatus->estaGenerada(),
        ];
    }

    /**
     * Resumen del estado actual del borrador para el agente.
     *
     * @return array<string, mixed>
     */
    public function resumenParaAgente(): array
    {
        return [
            'folio' => $this->folio,
            'obra' => $this->obra?->nombre,
            'proveedor' => $this->proveedor?->nombre,
            'fecha_requerida' => $this->fecha_requerida?->toDateString(),
            'partidas' => $this->items
                ->map(fn (OrdenCompraItem $item): array => [
                    'sku' => $item->material->sku,
                    'descripcion' => $item->material->descripcionCompleta(),
                    'cantidad' => (float) $item->cantidad,
                    'unidad' => $item->material->unidad->simbolo(),
                    'importe' => (float) $item->importe,
                ])
                ->all(),
            'subtotal' => (float) $this->subtotal,
            'iva' => (float) $this->iva,
            'total' => (float) $this->total,
            'faltantes' => $this->pendientes(),
            'lista_completa' => $this->estaCompleta(),
        ];
    }
}
