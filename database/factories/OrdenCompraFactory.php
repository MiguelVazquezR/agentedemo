<?php

namespace Database\Factories;

use App\Enums\EstatusOrdenCompra;
use App\Models\Obra;
use App\Models\OrdenCompra;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrdenCompra>
 */
class OrdenCompraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'folio' => null,
            'estatus' => EstatusOrdenCompra::Borrador,
            'conversacion_id' => null,
            'obra_id' => Obra::factory(),
            'proveedor_id' => Proveedor::factory(),
            'user_id' => User::factory(),
            'subtotal' => 0,
            'descuento' => 0,
            'iva' => 0,
            'total' => 0,
            'condiciones_pago' => 'Transferencia a 30 días',
            'fecha_requerida' => now()->addWeek(),
            'observaciones' => null,
            'generada_at' => null,
            'enviada_at' => null,
        ];
    }

    /**
     * Orden en construcción, todavía sin folio.
     */
    public function borrador(): static
    {
        return $this->state(fn (array $attributes): array => [
            'folio' => null,
            'estatus' => EstatusOrdenCompra::Borrador,
            'generada_at' => null,
        ]);
    }

    /**
     * Orden con folio ya asignado.
     */
    public function generada(): static
    {
        return $this->state(fn (array $attributes): array => [
            'folio' => 'OC-'.now()->format('Y').'-'.fake()->unique()->numerify('####'),
            'estatus' => EstatusOrdenCompra::Generada,
            'generada_at' => now(),
        ]);
    }

    /**
     * Orden ya enviada por correo al proveedor.
     */
    public function enviada(): static
    {
        return $this->generada()->state(fn (array $attributes): array => [
            'estatus' => EstatusOrdenCompra::Enviada,
            'enviada_at' => now(),
        ]);
    }

    /**
     * Orden cancelada.
     */
    public function cancelada(): static
    {
        return $this->generada()->state(fn (array $attributes): array => [
            'estatus' => EstatusOrdenCompra::Cancelada,
        ]);
    }
}
