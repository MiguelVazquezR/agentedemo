<?php

namespace App\Models;

use Database\Factories\OrdenCompraEnvioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $orden_compra_id
 * @property int|null $user_id
 * @property string $destinatario
 * @property string|null $cc
 * @property string $asunto
 * @property string|null $mensaje
 * @property string|null $pdf_archivo
 * @property bool $exito
 * @property string|null $error
 * @property Carbon|null $enviado_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read OrdenCompra $ordenCompra
 */
#[Fillable([
    'orden_compra_id',
    'user_id',
    'destinatario',
    'cc',
    'asunto',
    'mensaje',
    'pdf_archivo',
    'exito',
    'error',
    'enviado_at',
])]
class OrdenCompraEnvio extends Model
{
    /** @use HasFactory<OrdenCompraEnvioFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'exito' => 'boolean',
            'enviado_at' => 'datetime',
        ];
    }

    /**
     * Orden de compra enviada.
     *
     * @return BelongsTo<OrdenCompra, $this>
     */
    public function ordenCompra(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class);
    }

    /**
     * Usuario que realizó el envío.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Correos adicionales en copia.
     *
     * @return array<int, string>
     */
    public function copias(): array
    {
        return collect(explode(',', (string) $this->cc))
            ->map(fn (string $correo): string => trim($correo))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Datos del envío para el historial de la orden.
     *
     * @return array<string, mixed>
     */
    public function paraFrontend(): array
    {
        return [
            'id' => $this->id,
            'destinatario' => $this->destinatario,
            'copias' => $this->copias(),
            'asunto' => $this->asunto,
            'mensaje' => $this->mensaje,
            'exito' => $this->exito,
            'error' => $this->error,
            'usuario' => $this->user?->name,
            'enviado_en' => ($this->enviado_at ?? $this->created_at)?->toIso8601String(),
        ];
    }
}
