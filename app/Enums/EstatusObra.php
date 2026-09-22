<?php

namespace App\Enums;

enum EstatusObra: string
{
    case EnEjecucion = 'en_ejecucion';
    case Pausada = 'pausada';
    case Terminada = 'terminada';

    /**
     * Human readable label used across the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::EnEjecucion => 'En ejecución',
            self::Pausada => 'Pausada',
            self::Terminada => 'Terminada',
        };
    }

    /**
     * Options ready to be consumed by the frontend.
     *
     * @return array<int, array{valor: string, etiqueta: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case): array => ['valor' => $case->value, 'etiqueta' => $case->label()],
            self::cases(),
        );
    }
}
