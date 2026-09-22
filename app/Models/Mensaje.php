<?php

namespace App\Models;

use App\Enums\RolMensaje;
use Database\Factories\MensajeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $conversacion_id
 * @property RolMensaje $rol
 * @property string|null $contenido
 * @property array<int, array<string, mixed>>|null $herramientas
 * @property array<int, array<string, string>>|null $opciones
 * @property int|null $tokens_entrada
 * @property int|null $tokens_salida
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'conversacion_id',
    'rol',
    'contenido',
    'herramientas',
    'opciones',
    'tokens_entrada',
    'tokens_salida',
])]
class Mensaje extends Model
{
    /** @use HasFactory<MensajeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rol' => RolMensaje::class,
            'herramientas' => 'array',
            'opciones' => 'array',
            'tokens_entrada' => 'integer',
            'tokens_salida' => 'integer',
        ];
    }

    /**
     * Conversación a la que pertenece el mensaje.
     *
     * @return BelongsTo<Conversacion, $this>
     */
    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(Conversacion::class);
    }

    /**
     * Mensaje listo para renderizarse en el chat.
     *
     * @return array<string, mixed>
     */
    public function paraFrontend(): array
    {
        return [
            'id' => $this->id,
            'rol' => $this->rol->value,
            'contenido' => $this->contenido ?? '',
            'herramientas' => $this->herramientas ?? [],
            'opciones' => $this->opciones ?? [],
            'creado_en' => $this->created_at?->toIso8601String(),
        ];
    }
}
