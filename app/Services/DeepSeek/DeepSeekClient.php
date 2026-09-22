<?php

namespace App\Services\DeepSeek;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Cliente mínimo para la API de DeepSeek (compatible con OpenAI).
 *
 * @see https://api-docs.deepseek.com
 */
class DeepSeekClient
{
    /**
     * Envía la conversación al modelo y devuelve el mensaje del asistente.
     *
     * @param  array<int, array<string, mixed>>  $mensajes
     * @param  array<int, array<string, mixed>>  $herramientas
     * @return array{contenido: string|null, tool_calls: array<int, array<string, mixed>>, tokens_entrada: int|null, tokens_salida: int|null}
     */
    public function chat(array $mensajes, array $herramientas = []): array
    {
        $cuerpo = array_filter([
            'model' => $this->modelo(),
            'messages' => $mensajes,
            'tools' => $herramientas === [] ? null : $herramientas,
            'tool_choice' => $herramientas === [] ? null : 'auto',
            'max_tokens' => (int) config('agente.max_tokens'),
            'temperature' => (float) config('agente.temperatura'),
            'stream' => false,
            'thinking' => ['type' => 'disabled'],
        ], fn (mixed $valor): bool => $valor !== null);

        $respuesta = $this->enviar($cuerpo);

        $mensaje = $respuesta->json('choices.0.message');

        if (! is_array($mensaje)) {
            throw DeepSeekException::respuestaInvalida($respuesta->body());
        }

        Log::debug('DeepSeek respondió.', [
            'tokens_entrada' => $respuesta->json('usage.prompt_tokens'),
            'tokens_salida' => $respuesta->json('usage.completion_tokens'),
            'herramientas' => count($mensaje['tool_calls'] ?? []),
        ]);

        return [
            'contenido' => is_string($mensaje['content'] ?? null) ? $mensaje['content'] : null,
            'tool_calls' => is_array($mensaje['tool_calls'] ?? null) ? $mensaje['tool_calls'] : [],
            'tokens_entrada' => $respuesta->json('usage.prompt_tokens'),
            'tokens_salida' => $respuesta->json('usage.completion_tokens'),
        ];
    }

    /**
     * @param  array<string, mixed>  $cuerpo
     */
    private function enviar(array $cuerpo): Response
    {
        try {
            $respuesta = $this->peticion()->post('/chat/completions', $cuerpo);
        } catch (ConnectionException $excepcion) {
            throw DeepSeekException::sinConexion($excepcion);
        }

        if ($respuesta->failed()) {
            throw DeepSeekException::respuestaConError(
                $respuesta->status(),
                $respuesta->json('error.message') ?? $respuesta->body(),
            );
        }

        return $respuesta;
    }

    /**
     * Cliente HTTP configurado para la API de DeepSeek.
     */
    private function peticion(): PendingRequest
    {
        $clave = config('services.deepseek.key');

        if (! is_string($clave) || $clave === '') {
            throw DeepSeekException::sinCredenciales();
        }

        return Http::baseUrl((string) config('services.deepseek.base_url'))
            ->withToken($clave)
            ->acceptJson()
            ->asJson()
            ->connectTimeout(10)
            ->timeout((int) config('services.deepseek.timeout'))
            ->retry(
                [250, 800],
                when: fn (Throwable $excepcion): bool => $excepcion instanceof ConnectionException
                    || ($excepcion instanceof RequestException && ($excepcion->response->serverError() || $excepcion->response->status() === 429)),
                throw: false,
            );
    }

    private function modelo(): string
    {
        return (string) config('services.deepseek.model');
    }
}
