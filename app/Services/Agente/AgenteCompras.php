<?php

namespace App\Services\Agente;

use App\Enums\EstatusOrdenCompra;
use App\Enums\RolMensaje;
use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Models\OrdenCompra;
use App\Services\DeepSeek\DeepSeekClient;
use Illuminate\Support\Str;

/**
 * Orquesta la conversación con el modelo: arma el contexto, ejecuta las
 * herramientas que solicita y guarda la respuesta.
 */
class AgenteCompras
{
    public function __construct(
        private readonly DeepSeekClient $cliente,
        private readonly ContextoDelAgente $contexto,
        private readonly EjecutorDeHerramientas $ejecutor,
    ) {}

    /**
     * Procesa un mensaje del usuario y devuelve la respuesta del agente.
     */
    public function responder(Conversacion $conversacion, string $textoUsuario): Mensaje
    {
        $conversacion->mensajes()->create([
            'rol' => RolMensaje::Usuario,
            'contenido' => $textoUsuario,
        ]);

        $borrador = $this->borradorDe($conversacion);

        $mensajes = [
            ['role' => 'system', 'content' => $this->contexto->sistema($conversacion, $borrador)],
            ...$this->historial($conversacion),
        ];

        $pasos = [];
        $contenido = '';
        $tokensEntrada = 0;
        $tokensSalida = 0;

        for ($intento = 0; $intento < (int) config('agente.max_pasos'); $intento++) {
            $respuesta = $this->cliente->chat($mensajes, HerramientasDelAgente::definiciones());

            $tokensEntrada += (int) $respuesta['tokens_entrada'];
            $tokensSalida += (int) $respuesta['tokens_salida'];

            if ($respuesta['tool_calls'] === []) {
                $contenido = (string) $respuesta['contenido'];

                break;
            }

            $mensajes[] = [
                'role' => 'assistant',
                'content' => $respuesta['contenido'] ?? '',
                'tool_calls' => $respuesta['tool_calls'],
            ];

            foreach ($respuesta['tool_calls'] as $llamada) {
                [$paso, $resultado] = $this->ejecutarLlamada($llamada, $conversacion, $borrador);
                $pasos[] = $paso;

                $mensajes[] = [
                    'role' => 'tool',
                    'tool_call_id' => $resultado['tool_call_id'],
                    'content' => json_encode($resultado['contenido'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ];
            }
        }

        $opciones = $this->extraerOpciones($contenido);

        $mensaje = $conversacion->mensajes()->create([
            'rol' => RolMensaje::Agente,
            'contenido' => $contenido === '' ? 'Terminé de revisar la lista. ¿Quieres agregar o ajustar algo más?' : $contenido,
            'herramientas' => $pasos === [] ? null : $pasos,
            'opciones' => $opciones === [] ? null : $opciones,
            'tokens_entrada' => $tokensEntrada,
            'tokens_salida' => $tokensSalida,
        ]);

        $this->actualizarConversacion($conversacion, $textoUsuario);

        return $mensaje;
    }

    /**
     * Borrador de orden asociado a la conversación, se crea al primer uso.
     */
    public function borradorDe(Conversacion $conversacion): OrdenCompra
    {
        return $conversacion->borrador()->first() ?? OrdenCompra::query()->create([
            'estatus' => EstatusOrdenCompra::Borrador,
            'conversacion_id' => $conversacion->id,
            'obra_id' => $conversacion->obra_id,
            'user_id' => $conversacion->user_id,
        ]);
    }

    /**
     * Estado del borrador listo para el panel del frontend.
     *
     * @return array<string, mixed>
     */
    public function estadoDelBorrador(Conversacion $conversacion): array
    {
        $borrador = $this->borradorDe($conversacion)->load('items.material');

        return [
            ...$borrador->resumenParaAgente(),
            'id' => $borrador->id,
            'estatus' => $borrador->estatus->value,
            'estatus_etiqueta' => $borrador->estatus->label(),
            'estatus_clase' => $borrador->estatus->badgeClass(),
            'puede_generarse' => $borrador->puedeGenerarse(),
            'obra_id' => $borrador->obra_id,
            'proveedor_id' => $borrador->proveedor_id,
            'partidas' => $borrador->items->map(fn ($item): array => $item->paraFrontend())->all(),
        ];
    }

    /**
     * Ejecuta una llamada a herramienta solicitada por el modelo.
     *
     * @param  array<string, mixed>  $llamada
     * @return array{0: array{nombre: string, detalle: string, ok: bool}, 1: array{tool_call_id: string, contenido: array<string, mixed>}}
     */
    private function ejecutarLlamada(array $llamada, Conversacion $conversacion, OrdenCompra $borrador): array
    {
        $nombre = (string) data_get($llamada, 'function.name');
        $argumentos = json_decode((string) data_get($llamada, 'function.arguments', '{}'), true);

        $resultado = $this->ejecutor->ejecutar(
            $nombre,
            is_array($argumentos) ? $argumentos : [],
            $conversacion,
            $borrador,
        );

        return [
            ['nombre' => $nombre, 'detalle' => $resultado['resumen'], 'ok' => $resultado['ok']],
            ['tool_call_id' => (string) data_get($llamada, 'id'), 'contenido' => $resultado['contenido']],
        ];
    }

    /**
     * Historial reciente de la conversación (solo turnos de texto).
     *
     * @return array<int, array{role: string, content: string}>
     */
    private function historial(Conversacion $conversacion): array
    {
        return $conversacion->mensajes()
            ->whereIn('rol', [RolMensaje::Usuario->value, RolMensaje::Agente->value])
            ->orderByDesc('id')
            ->limit((int) config('agente.ventana_historial'))
            ->get()
            ->reverse()
            ->map(fn (Mensaje $mensaje): array => [
                'role' => $mensaje->rol === RolMensaje::Usuario ? 'user' : 'assistant',
                'content' => (string) $mensaje->contenido,
            ])
            ->values()
            ->all();
    }

    /**
     * Extrae la línea de opciones sugeridas y la quita del texto.
     *
     * @return array<int, array{etiqueta: string, valor: string}>
     */
    private function extraerOpciones(string &$texto): array
    {
        if ($texto === '' || ! preg_match('/^\s*OPCIONES:\s*(.+)$/mi', $texto, $coincidencias)) {
            return [];
        }

        $texto = trim((string) preg_replace('/^\s*OPCIONES:\s*.+$/mi', '', $texto));

        return collect(explode('|', $coincidencias[1]))
            ->map(fn (string $opcion): string => trim($opcion, " \t\n\r\0\x0B.-"))
            ->filter()
            ->take(4)
            ->map(fn (string $opcion): array => ['etiqueta' => $opcion, 'valor' => $opcion])
            ->values()
            ->all();
    }

    /**
     * Mantiene al día el título automático y la fecha del último mensaje.
     */
    private function actualizarConversacion(Conversacion $conversacion, string $textoUsuario): void
    {
        $conversacion->update([
            'ultimo_mensaje_at' => now(),
            'titulo' => $conversacion->titulo === Conversacion::TITULO_INICIAL
                ? Str::limit($textoUsuario, 60)
                : $conversacion->titulo,
        ]);
    }
}
