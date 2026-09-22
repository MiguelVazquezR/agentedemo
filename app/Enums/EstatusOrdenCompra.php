<?php

namespace App\Enums;

enum EstatusOrdenCompra: string
{
    case Borrador = 'borrador';
    case Completa = 'completa';
    case Generada = 'generada';
    case Enviada = 'enviada';
    case Autorizada = 'autorizada';
    case Cancelada = 'cancelada';

    /**
     * Human readable label used across the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Completa => 'Lista completa',
            self::Generada => 'Generada',
            self::Enviada => 'Enviada al proveedor',
            self::Autorizada => 'Autorizada',
            self::Cancelada => 'Cancelada',
        };
    }

    /**
     * Tailwind classes for the badge that renders this status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Borrador => 'bg-muted text-muted-foreground',
            self::Completa => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::Generada => 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
            self::Enviada => 'bg-violet-100 text-violet-800 dark:bg-violet-500/15 dark:text-violet-300',
            self::Autorizada => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
            self::Cancelada => 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-300',
        };
    }

    /**
     * A purchased order already left the draft stage.
     */
    public function estaGenerada(): bool
    {
        return $this !== self::Borrador && $this !== self::Completa;
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
