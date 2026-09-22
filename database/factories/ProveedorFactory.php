<?php

namespace Database\Factories;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proveedor>
 */
class ProveedorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ciudades = ['Monterrey', 'Guadalajara', 'Ciudad de México', 'Querétaro', 'Mérida', 'Puebla', 'Tijuana'];
        $nombre = fake()->unique()->company();

        return [
            'nombre' => $nombre,
            'razon_social' => $nombre.' S.A. de C.V.',
            'rfc' => strtoupper(fake()->lexify('????')).fake()->numerify('######').strtoupper(fake()->lexify('???')),
            'contacto' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'telefono' => fake()->numerify('## #### ####'),
            'ciudad' => fake()->randomElement($ciudades),
            'dias_credito' => fake()->randomElement([0, 0, 15, 30, 45]),
            'activo' => true,
        ];
    }

    /**
     * Proveedor que ya no se usa.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes): array => ['activo' => false]);
    }

    /**
     * Proveedor de contado.
     */
    public function deContado(): static
    {
        return $this->state(fn (array $attributes): array => ['dias_credito' => 0]);
    }
}
