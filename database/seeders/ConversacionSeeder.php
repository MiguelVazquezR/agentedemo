<?php

namespace Database\Seeders;

use App\Enums\EstatusConversacion;
use App\Enums\EstatusOrdenCompra;
use App\Enums\RolMensaje;
use App\Models\Conversacion;
use App\Models\Material;
use App\Models\Obra;
use App\Models\OrdenCompra;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConversacionSeeder extends Seeder
{
    /**
     * Conversación de ejemplo con una lista a medio armar, para que el
     * prospecto vea el panel de borrador con información real.
     */
    public function run(): void
    {
        $usuario = User::query()->where('email', 'demo@agentedemo.test')->firstOrFail();
        $obra = Obra::query()->where('codigo', 'OBR-001')->firstOrFail();

        $conversacion = Conversacion::query()->create([
            'user_id' => $usuario->id,
            'obra_id' => $obra->id,
            'titulo' => 'Cimentación casa 3 — Las Palmas',
            'estatus' => EstatusConversacion::Abierta,
            'ultimo_mensaje_at' => now()->subMinutes(12),
        ]);

        $this->agregarMensajes($conversacion, $obra);

        $borrador = OrdenCompra::query()->create([
            'estatus' => EstatusOrdenCompra::Borrador,
            'conversacion_id' => $conversacion->id,
            'obra_id' => $obra->id,
            'user_id' => $usuario->id,
            'fecha_requerida' => now()->addDays(10)->toDateString(),
            'condiciones_pago' => 'Transferencia a 30 días',
        ]);

        foreach ([
            ['Varilla corrugada grado 42', '3/8" (9.5 mm)', 40],
            ['Varilla corrugada grado 42', '1/2" (12.7 mm)', 15],
            ['Cemento Portland CPC', 'CPC 40', 30],
        ] as [$nombre, $medida, $cantidad]) {
            $borrador->agregarMaterial($this->material($nombre, $medida), $cantidad);
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
     * Guion corto que muestra cómo pregunta el agente.
     */
    private function agregarMensajes(Conversacion $conversacion, Obra $obra): void
    {
        $conversacion->mensajes()->create([
            'rol' => RolMensaje::Usuario,
            'contenido' => 'Buenos días, necesito material para la cimentación de la casa 3 en Las Palmas.',
        ]);

        $conversacion->mensajes()->create([
            'rol' => RolMensaje::Agente,
            'contenido' => "¡Buenos días! Ya tomé la obra {$obra->nombre}. Para armar la lista, ¿cuántos tramos de varilla necesitas y de qué calibre? Manejo estos:",
            'opciones' => [
                ['etiqueta' => '3/8" (9.5 mm)', 'valor' => 'Tramos de 3/8"'],
                ['etiqueta' => '1/2" (12.7 mm)', 'valor' => 'Tramos de 1/2"'],
                ['etiqueta' => '5/8" (15.9 mm)', 'valor' => 'Tramos de 5/8"'],
                ['etiqueta' => '3/4" (19.0 mm)', 'valor' => 'Tramos de 3/4"'],
            ],
        ]);

        $conversacion->mensajes()->create([
            'rol' => RolMensaje::Usuario,
            'contenido' => 'Son 40 tramos de 3/8 y 15 de 1/2. También 30 sacos de cemento CPC 40.',
        ]);

        $conversacion->mensajes()->create([
            'rol' => RolMensaje::Agente,
            'contenido' => 'Perfecto. Ya agregué a la lista 40 tramos de varilla 3/8", 15 tramos de 1/2" y 30 sacos de CPC 40. ¿Quieres que incluya también arena y grava para la cimentación?',
            'herramientas' => [
                ['nombre' => 'agregar_a_lista', 'detalle' => 'Varilla corrugada 3/8" × 40'],
                ['nombre' => 'agregar_a_lista', 'detalle' => 'Varilla corrugada 1/2" × 15'],
                ['nombre' => 'agregar_a_lista', 'detalle' => 'Cemento Portland CPC 40 × 30'],
            ],
            'opciones' => [
                ['etiqueta' => 'Sí, agrega arena y grava', 'valor' => 'Sí, agrega arena y grava para la cimentación'],
                ['etiqueta' => 'No, así está bien', 'valor' => 'No, así está bien'],
            ],
        ]);
    }
}
