<?php

namespace Database\Factories;

use App\Models\OrdenCompra;
use App\Models\OrdenCompraEnvio;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrdenCompraEnvio>
 */
class OrdenCompraEnvioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'orden_compra_id' => OrdenCompra::factory(),
            'user_id' => User::factory(),
            'destinatario' => fake()->safeEmail(),
            'cc' => null,
            'asunto' => 'Orden de compra '.fake()->bothify('OC-####-####'),
            'mensaje' => 'Adjunto encontrarás la orden de compra para surtir el material solicitado.',
            'pdf_archivo' => null,
            'exito' => true,
            'error' => null,
            'enviado_at' => now(),
        ];
    }

    /**
     * Intento de envío que falló.
     */
    public function fallido(): static
    {
        return $this->state(fn (array $attributes): array => [
            'exito' => false,
            'error' => 'No fue posible entregar el mensaje al destinatario.',
            'enviado_at' => now(),
        ]);
    }
}
