<?php

namespace Database\Seeders;

use App\Enums\EstatusConversacion;
use App\Enums\EstatusOrdenCompra;
use App\Enums\RolMensaje;
use App\Models\Conversacion;
use App\Models\Material;
use App\Models\Obra;
use App\Models\OrdenCompra;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrdenCompraSeeder extends Seeder
{
    /**
     * Historial de órdenes de compra para que el módulo y el tablero no
     * aparezcan vacíos durante la demostración.
     */
    public function run(): void
    {
        $usuario = User::query()->where('email', 'demo@agentedemo.test')->firstOrFail();

        foreach ($this->ordenes() as $datos) {
            $this->crearOrden($datos, $usuario);
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function crearOrden(array $datos, User $usuario): void
    {
        $obra = Obra::query()->where('codigo', $datos['obra'])->firstOrFail();
        $proveedor = Proveedor::query()->where('nombre', $datos['proveedor'])->firstOrFail();

        $conversacion = Conversacion::query()->create([
            'user_id' => $usuario->id,
            'obra_id' => $obra->id,
            'titulo' => $datos['folio'].' — '.$obra->nombre,
            'estatus' => EstatusConversacion::Finalizada,
            'ultimo_mensaje_at' => $datos['generada_at'],
        ]);

        $conversacion->mensajes()->create([
            'rol' => RolMensaje::Usuario,
            'contenido' => $datos['solicitud'],
        ]);

        $orden = OrdenCompra::query()->create([
            'folio' => $datos['folio'],
            'estatus' => $datos['estatus'],
            'conversacion_id' => $conversacion->id,
            'obra_id' => $obra->id,
            'proveedor_id' => $proveedor->id,
            'user_id' => $usuario->id,
            'condiciones_pago' => $proveedor->condicionDePago(),
            'fecha_requerida' => $datos['fecha_requerida'],
            'observaciones' => $datos['observaciones'],
            'generada_at' => $datos['generada_at'],
            'enviada_at' => $datos['enviada_at'],
        ]);

        $conversacion->mensajes()->create([
            'rol' => RolMensaje::Agente,
            'contenido' => $datos['respuesta'],
            'herramientas' => [
                ['nombre' => 'marcar_lista_terminada', 'detalle' => count($datos['items']).' partidas listas'],
            ],
        ]);

        foreach ($datos['items'] as [$nombre, $medida, $cantidad]) {
            $orden->agregarMaterial($this->material($nombre, $medida), $cantidad);
        }

        if ($datos['enviada_at'] !== null) {
            $orden->envios()->create([
                'user_id' => $usuario->id,
                'destinatario' => $proveedor->email,
                'asunto' => 'Orden de compra '.$orden->folio.' — '.$obra->nombre,
                'mensaje' => 'Buen día, adjunto la orden de compra para surtir el material solicitado.',
                'pdf_archivo' => 'ordenes/'.$orden->folio.'.pdf',
                'exito' => true,
                'enviado_at' => $datos['enviada_at'],
            ]);
        }
    }

    /**
     * Busca un material del catálogo por nombre y detalle
     * (calibre, color o presentación).
     */
    private function material(string $nombre, ?string $detalle = null): Material
    {
        return Material::query()
            ->where('nombre', $nombre)
            ->when($detalle !== null, fn ($query) => $query->where(function ($query) use ($detalle): void {
                $query->where('medida', $detalle)
                    ->orWhere('color', $detalle)
                    ->orWhere('presentacion', $detalle);
            }))
            ->firstOrFail();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function ordenes(): array
    {
        $anio = now()->format('Y');

        return [
            [
                'obra' => 'OBR-002',
                'proveedor' => 'Aceros del Norte',
                'folio' => 'OC-'.$anio.'-0001',
                'estatus' => EstatusOrdenCompra::Enviada,
                'generada_at' => now()->subDays(35),
                'enviada_at' => now()->subDays(35)->addMinutes(20),
                'fecha_requerida' => now()->addDays(3)->toDateString(),
                'observaciones' => 'Entregar en la bodega 4 del parque industrial. Descarga con grúa del proveedor.',
                'solicitud' => 'Necesito el acero de refuerzo para las losas de la bodega 4.',
                'respuesta' => 'Listo, integré los tres calibres que pediste más la malla y el alambre. La orden quedó enviada a Aceros del Norte.',
                'items' => [
                    ['Varilla corrugada grado 42', '3/4" (19.0 mm)', 60],
                    ['Varilla corrugada grado 42', '1/2" (12.7 mm)', 80],
                    ['Malla electrosoldada', '6x6 - 10/10', 10],
                    ['Alambre recocido', 'Rollo 10 kg', 4],
                ],
            ],
            [
                'obra' => 'OBR-003',
                'proveedor' => 'Pinturas y Acabados del Centro',
                'folio' => 'OC-'.$anio.'-0002',
                'estatus' => EstatusOrdenCompra::Generada,
                'generada_at' => now()->subDays(12),
                'enviada_at' => null,
                'fecha_requerida' => now()->addDays(12)->toDateString(),
                'observaciones' => 'Los colores son los que aprobó el cliente en la junta del lunes.',
                'solicitud' => 'Para el corporativo necesito pintura, sellador e impermeabilizante del mismo color que el año pasado.',
                'respuesta' => 'Dejé la lista completa con los colores aprobados. Falta enviarla al proveedor cuando la autorices.',
                'items' => [
                    ['Pintura vinílica', 'Blanco', 14],
                    ['Sellador 5x1', null, 6],
                    ['Impermeabilizante acrílico 5 años', 'Rojo óxido', 8],
                    ['Brocha', '4"', 10],
                ],
            ],
            [
                'obra' => 'OBR-001',
                'proveedor' => 'Concretos y Cementos MX',
                'folio' => 'OC-'.$anio.'-0003',
                'estatus' => EstatusOrdenCompra::Autorizada,
                'generada_at' => now()->subDays(5),
                'enviada_at' => null,
                'fecha_requerida' => now()->addDays(8)->toDateString(),
                'observaciones' => 'Colado programado a las 7:00 a.m., con bomba pluma.',
                'solicitud' => 'Ocupo el concreto de la losa de la casa 3, ya tengo el volumen calculado.',
                'respuesta' => 'Registré 18 m³ de concreto f\'c = 250 proveniente de la misma planta. La orden ya está autorizada y lista para enviar al proveedor.',
                'items' => [
                    ['Concreto premezclado', "f'c = 250 kg/cm²", 18],
                    ['Cemento Portland CPC', 'CPC 42.5R', 40],
                    ['Cal hidratada', 'Saco 25 kg', 10],
                ],
            ],
        ];
    }
}
