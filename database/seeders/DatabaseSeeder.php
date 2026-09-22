<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Los seeders de compras no desactivan los eventos del modelo a
     * propósito: los importes y totales deben calcularse igual que en
     * la aplicación.
     */
    public function run(): void
    {
        $this->call([
            ObraSeeder::class,
            ProveedorSeeder::class,
            MaterialSeeder::class,
            UsuarioDemoSeeder::class,
            ConversacionSeeder::class,
            OrdenCompraSeeder::class,
        ]);
    }
}
