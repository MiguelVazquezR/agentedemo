<?php

namespace App\Enums;

enum RolMensaje: string
{
    case Usuario = 'user';
    case Agente = 'assistant';
    case Herramienta = 'tool';

    /**
     * Human readable label used across the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Usuario => 'Usuario',
            self::Agente => 'Agente IA',
            self::Herramienta => 'Herramienta',
        };
    }
}
