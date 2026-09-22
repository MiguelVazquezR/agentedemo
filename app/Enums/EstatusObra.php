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
     * Tailwind classes for the badge that renders this status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::EnEjecucion => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
            self::Pausada => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::Terminada => 'bg-muted text-muted-foreground',
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
