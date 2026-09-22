<?php

namespace App\Services\DeepSeek;

use RuntimeException;
use Throwable;

/**
 * Error controlado al hablar con el servicio del agente.
 */
class DeepSeekException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $contexto
     */
    public function __construct(
        string $mensaje,
        private readonly array $contexto = [],
        ?Throwable $anterior = null,
    ) {
        parent::__construct($mensaje, 0, $anterior);
    }

    /**
     * Datos extra que se agregan al registro del error.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->contexto;
    }

    public static function sinCredenciales(): self
    {
        return new self('El agente no está configurado: falta la clave de DeepSeek en el archivo .env.');
    }

    public static function sinConexion(Throwable $anterior): self
    {
        return new self('No pude comunicarme con el agente. Revisa la conexión e inténtalo de nuevo.', [], $anterior);
    }

    public static function respuestaConError(int $estatus, ?string $detalle): self
    {
        return new self(
            'El agente no pudo responder en este momento. Inténtalo de nuevo en unos segundos.',
            ['estatus' => $estatus, 'detalle' => $detalle],
        );
    }

    public static function respuestaInvalida(?string $detalle = null): self
    {
        return new self(
            'El agente devolvió una respuesta que no se pudo interpretar. Inténtalo de nuevo.',
            ['detalle' => $detalle],
        );
    }
}
