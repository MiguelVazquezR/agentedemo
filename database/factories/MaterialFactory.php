<?php

namespace Database\Factories;

use App\Enums\CategoriaMaterial;
use App\Enums\UnidadMedida;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->randomElement([
            'Varilla corrugada',
            'Cemento Portland CPC',
            'Block de concreto',
            'Impermeabilizante acrílico',
            'Tubo PVC hidráulico',
            'Cable THW',
        ]);

        return [
            'sku' => 'MAT-'.fake()->unique()->numerify('#####'),
            'nombre' => $nombre,
            'categoria' => fake()->randomElement(CategoriaMaterial::cases()),
            'familia' => $nombre,
            'marca' => fake()->randomElement(['Cemex', 'Truper', 'Rotoplas', 'Deacero', 'Comex', null]),
            'unidad' => fake()->randomElement(UnidadMedida::cases()),
            'presentacion' => fake()->randomElement(['12 m', '50 kg', '19 L', '100 m', null]),
            'medida' => fake()->randomElement(['3/8"', '1/2"', '20 mm', '1.20 x 2.40 m', null]),
            'color' => fake()->randomElement(['Natural', 'Blanco', 'Rojo óxido', 'Gris', null]),
            'precio_unitario' => fake()->randomFloat(2, 45, 4800),
            'moneda' => 'MXN',
            'especificaciones' => null,
            'proveedor_preferido_id' => null,
            'activo' => true,
        ];
    }

    /**
     * Material perteneciente a una familia concreta del catálogo.
     */
    public function deFamilia(string $familia, CategoriaMaterial $categoria, UnidadMedida $unidad): static
    {
        return $this->state(fn (array $attributes): array => [
            'nombre' => $familia,
            'familia' => $familia,
            'categoria' => $categoria,
            'unidad' => $unidad,
        ]);
    }
}
