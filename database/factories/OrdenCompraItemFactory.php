<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrdenCompraItem>
 */
class OrdenCompraItemFactory extends Factory
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
            'material_id' => Material::factory(),
            'cantidad' => fake()->numberBetween(1, 50),
            'precio_unitario' => fake()->randomFloat(2, 45, 3600),
            'notas' => null,
            'orden' => 1,
        ];
    }

    /**
     * Partida con una cantidad y precio concretos.
     */
    public function conCantidad(float $cantidad): static
    {
        return $this->state(fn (array $attributes): array => ['cantidad' => $cantidad]);
    }
}
