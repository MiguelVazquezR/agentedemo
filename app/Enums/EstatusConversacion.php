<?php

namespace App\Enums;

enum EstatusConversacion: string
{
    case Abierta = 'abierta';
    case Finalizada = 'finalizada';

    /**
     * Human readable label used across the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Abierta => 'Abierta',
            self::Finalizada => 'Finalizada',
        };
    }
}
