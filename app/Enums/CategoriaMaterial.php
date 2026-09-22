<?php

namespace App\Enums;

enum CategoriaMaterial: string
{
    case Cementos = 'cementos';
    case Aceros = 'aceros';
    case Agregados = 'agregados';
    case Albanileria = 'albanileria';
    case Impermeabilizacion = 'impermeabilizacion';
    case Plomeria = 'plomeria';
    case Electrico = 'electrico';
    case Acabados = 'acabados';
    case Herramienta = 'herramienta';
    case Seguridad = 'seguridad';

    /**
     * Human readable label used across the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Cementos => 'Cementos y morteros',
            self::Aceros => 'Acero de refuerzo',
            self::Agregados => 'Agregados',
            self::Albanileria => 'Albañilería',
            self::Impermeabilizacion => 'Impermeabilización',
            self::Plomeria => 'Plomería e hidráulica',
            self::Electrico => 'Eléctrico e iluminación',
            self::Acabados => 'Acabados',
            self::Herramienta => 'Herramienta',
            self::Seguridad => 'Seguridad y EPP',
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
