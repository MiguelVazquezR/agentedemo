<?php

namespace App\Models;

use App\Enums\EstatusObra;
use Database\Factories\ObraFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string $cliente
 * @property string $direccion
 * @property string $ciudad
 * @property string $estado
 * @property string $responsable
 * @property string|null $telefono_contacto
 * @property string $presupuesto_autorizado
 * @property Carbon $fecha_inicio
 * @property Carbon|null $fecha_fin_estimada
 * @property EstatusObra $estatus
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int|null $ordenes_compra_sum_total
 * @property-read int|null $ordenes_compra_count
 * @property-read int|null $ordenes_generadas_count
 * @property-read string|null $ordenes_generadas_sum_total
 */
#[Fillable([
    'codigo',
    'nombre',
    'cliente',
    'direccion',
    'ciudad',
    'estado',
    'responsable',
    'telefono_contacto',
    'presupuesto_autorizado',
    'fecha_inicio',
    'fecha_fin_estimada',
    'estatus',
])]
class Obra extends Model
{
    /** @use HasFactory<ObraFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'presupuesto_autorizado' => 'decimal:2',
            'fecha_inicio' => 'date',
            'fecha_fin_estimada' => 'date',
            'estatus' => EstatusObra::class,
        ];
    }

    /**
     * Conversaciones del agente ligadas a esta obra.
     *
     * @return HasMany<Conversacion, $this>
     */
    public function conversaciones(): HasMany
    {
        return $this->hasMany(Conversacion::class);
    }

    /**
     * Órdenes de compra solicitadas para esta obra.
     *
     * @return HasMany<OrdenCompra, $this>
     */
    public function ordenesCompra(): HasMany
    {
        return $this->hasMany(OrdenCompra::class);
    }

    /**
     * Nombre corto de la ubicación de la obra.
     */
    public function ubicacion(): string
    {
        return "{$this->ciudad}, {$this->estado}";
    }

    /**
     * Órdenes de compra ya generadas para esta obra.
     *
     * @return HasMany<OrdenCompra, $this>
     */
    public function ordenesGeneradas(): HasMany
    {
        return $this->hasMany(OrdenCompra::class)->generadas();
    }
}
