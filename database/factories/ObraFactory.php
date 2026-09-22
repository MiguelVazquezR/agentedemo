<?php

namespace Database\Factories;

use App\Enums\EstatusObra;
use App\Models\Obra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Obra>
 */
class ObraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ubicaciones = [
            ['Monterrey', 'Nuevo León'],
            ['Guadalajara', 'Jalisco'],
            ['Ciudad de México', 'Ciudad de México'],
            ['Querétaro', 'Querétaro'],
            ['Mérida', 'Yucatán'],
            ['Puebla', 'Puebla'],
        ];

        [$ciudad, $estado] = fake()->randomElement($ubicaciones);

        return [
            'codigo' => 'OBR-'.fake()->unique()->numerify('###'),
            'nombre' => fake()->randomElement([
                'Residencial', 'Nave industrial', 'Plaza comercial', 'Remodelación', 'Ampliación de bodega',
            ]).' '.fake()->lastName(),
            'cliente' => fake()->company(),
            'direccion' => fake()->streetAddress(),
            'ciudad' => $ciudad,
            'estado' => $estado,
            'responsable' => fake()->name(),
            'telefono_contacto' => fake()->numerify('81########'),
            'presupuesto_autorizado' => fake()->randomFloat(2, 250000, 12000000),
            'fecha_inicio' => fake()->dateTimeBetween('-8 months', 'now'),
            'fecha_fin_estimada' => fake()->dateTimeBetween('now', '+14 months'),
            'estatus' => EstatusObra::EnEjecucion,
        ];
    }

    /**
     * Obra que ya terminó.
     */
    public function terminada(): static
    {
        return $this->state(fn (array $attributes): array => [
            'estatus' => EstatusObra::Terminada,
            'fecha_fin_estimada' => now()->subMonth(),
        ]);
    }
}
