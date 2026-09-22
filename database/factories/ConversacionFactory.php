<?php

namespace Database\Factories;

use App\Enums\EstatusConversacion;
use App\Models\Conversacion;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversacion>
 */
class ConversacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'obra_id' => null,
            'titulo' => 'Lista de materiales '.now()->format('d/m/Y'),
            'estatus' => EstatusConversacion::Abierta,
            'ultimo_mensaje_at' => now(),
        ];
    }

    /**
     * Conversación ya ligada a una obra.
     */
    public function conObra(?Obra $obra = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'obra_id' => $obra instanceof Obra ? $obra->id : Obra::factory(),
        ]);
    }
}
