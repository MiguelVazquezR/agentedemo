<?php

namespace Database\Factories;

use App\Enums\RolMensaje;
use App\Models\Conversacion;
use App\Models\Mensaje;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mensaje>
 */
class MensajeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversacion_id' => Conversacion::factory(),
            'rol' => RolMensaje::Usuario,
            'contenido' => 'Necesito material para la obra: '.fake()->sentence(8),
            'herramientas' => null,
            'opciones' => null,
            'tokens_entrada' => null,
            'tokens_salida' => null,
        ];
    }

    /**
     * Respuesta generada por el agente.
     */
    public function delAgente(): static
    {
        return $this->state(fn (array $attributes): array => [
            'rol' => RolMensaje::Agente,
            'contenido' => 'Claro, ¿de qué medida y presentación lo necesitas?',
            'opciones' => [
                ['etiqueta' => '3/8"', 'valor' => '3/8 pulgada'],
                ['etiqueta' => '1/2"', 'valor' => '1/2 pulgada'],
            ],
        ]);
    }
}
