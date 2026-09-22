<?php

namespace Database\Seeders;

use App\Enums\EstatusObra;
use App\Models\Obra;
use Illuminate\Database\Seeder;

class ObraSeeder extends Seeder
{
    /**
     * Obras de demostración donde se solicitan los materiales.
     */
    public function run(): void
    {
        foreach ($this->obras() as $obra) {
            Obra::query()->updateOrCreate(
                ['codigo' => $obra['codigo']],
                $obra,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function obras(): array
    {
        return [
            [
                'codigo' => 'OBR-001',
                'nombre' => 'Residencial Las Palmas — Etapa 2',
                'cliente' => 'Grupo Inmobiliario Las Palmas',
                'direccion' => 'Av. Las Palmas 1200, Col. Valle Oriente',
                'ciudad' => 'Monterrey',
                'estado' => 'Nuevo León',
                'responsable' => 'Ing. Daniela Treviño',
                'telefono_contacto' => '81 8340 1122',
                'presupuesto_autorizado' => 18450000.00,
                'fecha_inicio' => '2026-02-03',
                'fecha_fin_estimada' => '2027-03-30',
                'estatus' => EstatusObra::EnEjecucion,
            ],
            [
                'codigo' => 'OBR-002',
                'nombre' => 'Nave Industrial Parque Norte — Bodega 4',
                'cliente' => 'Logística del Norte, S.A. de C.V.',
                'direccion' => 'Carretera Miguel Alemán Km 18, Parque Industrial Norte',
                'ciudad' => 'Apodaca',
                'estado' => 'Nuevo León',
                'responsable' => 'Ing. Raúl Cepeda',
                'telefono_contacto' => '81 8123 9988',
                'presupuesto_autorizado' => 27800000.00,
                'fecha_inicio' => '2026-05-18',
                'fecha_fin_estimada' => '2027-01-15',
                'estatus' => EstatusObra::EnEjecucion,
            ],
            [
                'codigo' => 'OBR-003',
                'nombre' => 'Remodelación Corporativo San Pedro',
                'cliente' => 'Servicios Corporativos Monterrey',
                'direccion' => 'Calzada del Valle 400, Col. Del Valle',
                'ciudad' => 'San Pedro Garza García',
                'estado' => 'Nuevo León',
                'responsable' => 'Arq. Sofía Elizondo',
                'telefono_contacto' => '81 8368 4411',
                'presupuesto_autorizado' => 6350000.00,
                'fecha_inicio' => '2026-07-01',
                'fecha_fin_estimada' => '2026-12-20',
                'estatus' => EstatusObra::EnEjecucion,
            ],
            [
                'codigo' => 'OBR-004',
                'nombre' => 'Ampliación Bodega Central de Abasto',
                'cliente' => 'Comercializadora del Centro',
                'direccion' => 'Blvd. Carlos Salinas de Gortari 205, Central de Abasto',
                'ciudad' => 'Guadalajara',
                'estado' => 'Jalisco',
                'responsable' => 'Ing. Marco Uribe',
                'telefono_contacto' => '33 3812 5544',
                'presupuesto_autorizado' => 9250000.00,
                'fecha_inicio' => '2026-03-10',
                'fecha_fin_estimada' => '2027-02-28',
                'estatus' => EstatusObra::Pausada,
            ],
        ];
    }
}
