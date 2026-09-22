<?php

namespace App\Models;

use Database\Factories\OrdenCompraItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $orden_compra_id
 * @property int $material_id
 * @property string|float $cantidad
 * @property string|float $precio_unitario
 * @property string|float $importe
 * @property string|null $notas
 * @property int $orden
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Material $material
 * @property-read OrdenCompra|null $ordenCompra
 */
#[Fillable(['orden_compra_id', 'material_id', 'cantidad', 'precio_unitario', 'notas', 'orden'])]
class OrdenCompraItem extends Model
{
    /** @use HasFactory<OrdenCompraItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'importe' => 'decimal:2',
            'orden' => 'integer',
        ];
    }

    /**
     * El importe siempre se deriva de cantidad por precio unitario.
     */
    protected static function booted(): void
    {
        static::saving(function (OrdenCompraItem $item): void {
            $item->importe = round((float) $item->cantidad * (float) $item->precio_unitario, 2);
        });

        static::saved(function (OrdenCompraItem $item): void {
            $item->ordenCompra?->recalcularTotales();
        });

        static::deleted(function (OrdenCompraItem $item): void {
            $item->ordenCompra?->recalcularTotales();
        });
    }

    /**
     * Material del catálogo que representa esta partida.
     *
     * @return BelongsTo<Material, $this>
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Orden de compra a la que pertenece la partida.
     *
     * @return BelongsTo<OrdenCompra, $this>
     */
    public function ordenCompra(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class);
    }

    /**
     * Etiqueta de cantidad con su unidad, por ejemplo "12 pza".
     */
    public function cantidadConUnidad(): string
    {
        $cantidad = (float) $this->cantidad;
        $formateada = rtrim(rtrim(number_format($cantidad, 2, '.', ','), '0'), '.');

        return $formateada.' '.$this->material->unidad->simbolo();
    }

    /**
     * Vista previa usada por las tarjetas del borrador.
     *
     * @return array<string, mixed>
     */
    public function paraFrontend(): array
    {
        return [
            'id' => $this->id,
            'material_id' => $this->material_id,
            'sku' => $this->material->sku,
            'descripcion' => $this->material->descripcionCompleta(),
            'unidad' => $this->material->unidad->value,
            'unidad_simbolo' => $this->material->unidad->simbolo(),
            'cantidad' => (float) $this->cantidad,
            'precio_unitario' => (float) $this->precio_unitario,
            'importe' => (float) $this->importe,
            'notas' => $this->notas,
        ];
    }
}
