<?php

namespace App\Enums;

enum UnidadMedida: string
{
    case Pieza = 'pieza';
    case Saco = 'saco';
    case Tonelada = 'tonelada';
    case Kilogramo = 'kg';
    case MetroCubico = 'm3';
    case MetroCuadrado = 'm2';
    case MetroLineal = 'ml';
    case Litro = 'litro';
    case Cubeta = 'cubeta';
    case Rollo = 'rollo';
    case Caja = 'caja';
    case Servicio = 'servicio';

    /**
     * Human readable label used across the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pieza => 'Pieza',
            self::Saco => 'Saco',
            self::Tonelada => 'Tonelada',
            self::Kilogramo => 'Kilogramo',
            self::MetroCubico => 'Metro cúbico',
            self::MetroCuadrado => 'Metro cuadrado',
            self::MetroLineal => 'Metro lineal',
            self::Litro => 'Litro',
            self::Cubeta => 'Cubeta',
            self::Rollo => 'Rollo',
            self::Caja => 'Caja',
            self::Servicio => 'Servicio',
        };
    }

    /**
     * Short symbol used inside tables and purchase order lines.
     */
    public function simbolo(): string
    {
        return match ($this) {
            self::Pieza => 'pza',
            self::Saco => 'saco',
            self::Tonelada => 'ton',
            self::Kilogramo => 'kg',
            self::MetroCubico => 'm³',
            self::MetroCuadrado => 'm²',
            self::MetroLineal => 'ml',
            self::Litro => 'L',
            self::Cubeta => 'cubeta',
            self::Rollo => 'rollo',
            self::Caja => 'caja',
            self::Servicio => 'serv',
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
