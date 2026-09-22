<?php

use App\Services\Agente\HerramientasDelAgente;

test('las herramientas se codifican como esquemas válidos para la API', function () {
    $definiciones = HerramientasDelAgente::definiciones();
    $codificado = (string) json_encode($definiciones);

    expect($definiciones)->toHaveCount(11)
        ->and(str_contains($codificado, '"properties":[]'))->toBeFalse()
        ->and(str_contains($codificado, '"properties":{}'))->toBeTrue()
        ->and(str_contains($codificado, '"tool_choice"'))->toBeFalse();
});

test('cada herramienta declara nombre, descripción y parámetros', function () {
    foreach (HerramientasDelAgente::definiciones() as $definicion) {
        expect($definicion['type'])->toBe('function')
            ->and($definicion['function']['name'])->not->toBeEmpty()
            ->and($definicion['function']['description'])->not->toBeEmpty()
            ->and($definicion['function']['parameters']['type'])->toBe('object');
    }
});
