<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsuarioDemoSeeder extends Seeder
{
    /**
     * Usuario con el que se presenta la demostración.
     */
    public function run(): void
    {
        $usuario = User::query()->updateOrCreate(
            ['email' => 'demo@agentedemo.test'],
            [
                'name' => 'Miguel Vázquez',
                'password' => 'password',
            ],
        );

        $usuario->forceFill(['email_verified_at' => now()])->save();
    }
}
