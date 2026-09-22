<?php

namespace App\Models;

use Database\Factories\ProveedorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nombre
 * @property string $razon_social
 * @property string $rfc
 * @property string $contacto
 * @property string $email
 * @property string $telefono
 * @property string $ciudad
 * @property int $dias_credito
 * @property bool $activo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Table('proveedores')]
#[Fillable([
    'nombre',
    'razon_social',
    'rfc',
    'contacto',
    'email',
    'telefono',
    'ciudad',
    'dias_credito',
    'activo',
])]
class Proveedor extends Model
{
    /** @use HasFactory<ProveedorFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dias_credito' => 'integer',
            'activo' => 'boolean',
        ];
    }

    /**
     * Catálogo que surte preferentemente este proveedor.
     *
     * @return HasMany<Material, $this>
     */
    public function materiales(): HasMany
    {
        return $this->hasMany(Material::class, 'proveedor_preferido_id');
    }

    /**
     * Órdenes de compra asignadas a este proveedor.
     *
     * @return HasMany<OrdenCompra, $this>
     */
    public function ordenesCompra(): HasMany
    {
        return $this->hasMany(OrdenCompra::class);
    }

    /**
     * Condición de crédito lista para mostrarse en la orden.
     */
    public function condicionDePago(): string
    {
        return $this->dias_credito > 0
            ? "Crédito {$this->dias_credito} días"
            : 'Contado';
    }
}
