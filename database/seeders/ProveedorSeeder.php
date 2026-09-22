<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    /**
     * Proveedores de demostración con los que se puede surtir una orden.
     */
    public function run(): void
    {
        foreach ($this->proveedores() as $proveedor) {
            Proveedor::query()->updateOrCreate(
                ['nombre' => $proveedor['nombre']],
                $proveedor,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function proveedores(): array
    {
        return [
            [
                'nombre' => 'Concretos y Cementos MX',
                'razon_social' => 'Concretos y Cementos MX, S.A. de C.V.',
                'rfc' => 'CCM980415J23',
                'contacto' => 'Ricardo Betancourt',
                'email' => 'cotizaciones@concretosmx.com.mx',
                'telefono' => '81 8365 4400',
                'ciudad' => 'San Nicolás de los Garza',
                'dias_credito' => 15,
            ],
            [
                'nombre' => 'Aceros del Norte',
                'razon_social' => 'Aceros y Perfiles del Norte, S.A. de C.V.',
                'rfc' => 'APN110827K12',
                'contacto' => 'Verónica Salinas',
                'email' => 'pedidos@acerosdelnorte.mx',
                'telefono' => '81 8123 9087',
                'ciudad' => 'Apodaca',
                'dias_credito' => 45,
            ],
            [
                'nombre' => 'Materiales El Águila',
                'razon_social' => 'Materiales El Águila, S.A. de C.V.',
                'rfc' => 'MEA150312H45',
                'contacto' => 'Jorge Villalobos',
                'email' => 'ventas@materialeselaguila.mx',
                'telefono' => '81 8345 2210',
                'ciudad' => 'Monterrey',
                'dias_credito' => 30,
            ],
            [
                'nombre' => 'Hidráulica y Plomería Moderna',
                'razon_social' => 'Hidráulica Moderna de Occidente, S.A. de C.V.',
                'rfc' => 'HMO120630P91',
                'contacto' => 'Carlos Ramírez',
                'email' => 'contacto@hiplommoderna.mx',
                'telefono' => '33 3812 7766',
                'ciudad' => 'Guadalajara',
                'dias_credito' => 0,
            ],
            [
                'nombre' => 'Pinturas y Acabados del Centro',
                'razon_social' => 'Acabados del Centro, S.A. de C.V.',
                'rfc' => 'ACE160218M34',
                'contacto' => 'Laura Domínguez',
                'email' => 'pedidos@acabadoscentro.mx',
                'telefono' => '442 224 8899',
                'ciudad' => 'Querétaro',
                'dias_credito' => 30,
            ],
            [
                'nombre' => 'Eléctricos y Seguridad Industrial',
                'razon_social' => 'Suministros Eléctricos Industriales, S.A. de C.V.',
                'rfc' => 'SEI071105R56',
                'contacto' => 'Héctor Fuentes',
                'email' => 'ventas@suministroselectricos.mx',
                'telefono' => '55 5567 1200',
                'ciudad' => 'Ciudad de México',
                'dias_credito' => 30,
            ],
        ];
    }
}
