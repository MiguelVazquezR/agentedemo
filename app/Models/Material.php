<?php

namespace App\Models;

use App\Enums\CategoriaMaterial;
use App\Enums\UnidadMedida;
use Database\Factories\MaterialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $sku
 * @property string $nombre
 * @property CategoriaMaterial $categoria
 * @property string $familia
 * @property string|null $marca
 * @property UnidadMedida $unidad
 * @property string|null $presentacion
 * @property string|null $medida
 * @property string|null $color
 * @property string $precio_unitario
 * @property string $moneda
 * @property array<string, mixed>|null $especificaciones
 * @property int|null $proveedor_preferido_id
 * @property bool $activo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Proveedor|null $proveedorPreferido
 */
#[Table('materiales')]
#[Fillable([
    'sku',
    'nombre',
    'categoria',
    'familia',
    'marca',
    'unidad',
    'presentacion',
    'medida',
    'color',
    'precio_unitario',
    'moneda',
    'especificaciones',
    'proveedor_preferido_id',
    'activo',
])]
class Material extends Model
{
    /** @use HasFactory<MaterialFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'categoria' => CategoriaMaterial::class,
            'unidad' => UnidadMedida::class,
            'precio_unitario' => 'decimal:2',
            'especificaciones' => 'array',
            'activo' => 'boolean',
        ];
    }

    /**
     * Proveedor que surte este material por defecto.
     *
     * @return BelongsTo<Proveedor, $this>
     */
    public function proveedorPreferido(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_preferido_id');
    }

    /**
     * Partidas de órdenes de compra que usan este material.
     *
     * @return HasMany<OrdenCompraItem, $this>
     */
    public function ordenCompraItems(): HasMany
    {
        return $this->hasMany(OrdenCompraItem::class);
    }

    /**
     * Scope: búsqueda libre por nombre, medida, color, marca, familia o SKU.
     *
     * @param  Builder<Material>  $query
     */
    public function scopeBuscar(Builder $query, string $termino): void
    {
        $termino = '%'.trim($termino).'%';

        $query->where(function (Builder $query) use ($termino): void {
            $query->whereLike('nombre', $termino)
                ->orWhereLike('familia', $termino)
                ->orWhereLike('marca', $termino)
                ->orWhereLike('medida', $termino)
                ->orWhereLike('color', $termino)
                ->orWhereLike('presentacion', $termino)
                ->orWhereLike('sku', $termino);
        });
    }

    /**
     * Descripción completa de la partida, tal como se imprime en la orden.
     */
    public function descripcionCompleta(): string
    {
        $detalles = array_filter([
            $this->medida,
            $this->presentacion,
            $this->color,
            $this->marca,
        ]);

        return trim($this->nombre.($detalles === [] ? '' : ' '.implode(' · ', $detalles)));
    }

    /**
     * Representación compacta del material para el agente y las sugerencias.
     *
     * @return array<string, mixed>
     */
    public function paraAgente(): array
    {
        return [
            'sku' => $this->sku,
            'nombre' => $this->nombre,
            'marca' => $this->marca,
            'medida' => $this->medida,
            'presentacion' => $this->presentacion,
            'color' => $this->color,
            'unidad' => $this->unidad->value,
            'precio_unitario' => (float) $this->precio_unitario,
        ];
    }
}
