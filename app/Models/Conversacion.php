<?php

namespace App\Models;

use App\Enums\EstatusConversacion;
use App\Enums\EstatusOrdenCompra;
use Database\Factories\ConversacionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $obra_id
 * @property string $titulo
 * @property EstatusConversacion $estatus
 * @property Carbon|null $ultimo_mensaje_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Obra|null $obra
 * @property-read OrdenCompra|null $borrador
 * @property-read OrdenCompra|null $ordenGenerada
 */
#[Table('conversaciones')]
#[Fillable(['user_id', 'obra_id', 'titulo', 'estatus', 'ultimo_mensaje_at'])]
class Conversacion extends Model
{
    /** @use HasFactory<ConversacionFactory> */
    use HasFactory;

    /**
     * Título con el que nace una conversación nueva.
     */
    public const TITULO_INICIAL = 'Nueva conversación';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estatus' => EstatusConversacion::class,
            'ultimo_mensaje_at' => 'datetime',
        ];
    }

    /**
     * Usuario que sostiene la conversación con el agente.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obra sobre la que se está cotizando material.
     *
     * @return BelongsTo<Obra, $this>
     */
    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    /**
     * Mensajes intercambiados con el agente.
     *
     * @return HasMany<Mensaje, $this>
     */
    public function mensajes(): HasMany
    {
        return $this->hasMany(Mensaje::class);
    }

    /**
     * Orden de compra en construcción durante la conversación.
     *
     * @return HasOne<OrdenCompra, $this>
     */
    public function borrador(): HasOne
    {
        return $this->hasOne(OrdenCompra::class)
            ->whereIn('estatus', [EstatusOrdenCompra::Borrador, EstatusOrdenCompra::Completa])
            ->latestOfMany();
    }

    /**
     * Última orden de compra ya generada desde esta conversación.
     *
     * @return HasOne<OrdenCompra, $this>
     */
    public function ordenGenerada(): HasOne
    {
        return $this->hasOne(OrdenCompra::class)
            ->whereNotIn('estatus', [EstatusOrdenCompra::Borrador, EstatusOrdenCompra::Completa])
            ->latestOfMany();
    }

    /**
     * El agente ya cerró la lista de materiales.
     */
    public function listaTerminada(): bool
    {
        return $this->borrador?->estatus === EstatusOrdenCompra::Completa;
    }
}
