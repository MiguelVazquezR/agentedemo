<?php

use App\Models\Conversacion;
use App\Models\Material;
use App\Models\Mensaje;
use App\Models\OrdenCompra;
use App\Models\User;
use App\Services\Agente\AgenteCompras;
use App\Services\DeepSeek\DeepSeekException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('agrega a la lista el material que solicita el agente', function () {
    Http::preventStrayRequests();
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create(['titulo' => Conversacion::TITULO_INICIAL]);
    $material = Material::factory()->create(['sku' => 'ACE-001', 'nombre' => 'Varilla corrugada', 'precio_unitario' => 129]);

    Http::fake([
        'api.deepseek.com/chat/completions' => Http::sequence()
            ->push(respuestaDeHerramienta('agregar_a_lista', ['sku' => $material->sku, 'cantidad' => 40]))
            ->push(respuestaDeTexto('Listo, agregué 40 tramos.')),
    ]);

    $respuesta = app(AgenteCompras::class)->responder($conversacion, 'Necesito 40 tramos de varilla 3/8');

    $borrador = $conversacion->borrador()->firstOrFail();

    expect($borrador->items()->count())->toBe(1)
        ->and($borrador->subtotal)->toBe('5160.00')
        ->and($respuesta->contenido)->toBe('Listo, agregué 40 tramos.')
        ->and($respuesta->herramientas)->toHaveCount(1)
        ->and($conversacion->refresh()->titulo)->toBe('Necesito 40 tramos de varilla 3/8');
});

test('guarda las opciones sugeridas y las quita del texto', function () {
    Http::preventStrayRequests();
    $conversacion = Conversacion::factory()->create();

    Http::fake([
        'api.deepseek.com/chat/completions' => Http::response(respuestaDeTexto(
            "¿De qué calibre lo necesitas?\n\nOPCIONES: 3/8 pulgada | 1/2 pulgada | 3/4 pulgada",
        )),
    ]);

    $respuesta = app(AgenteCompras::class)->responder($conversacion, 'Necesito varilla');

    expect($respuesta->contenido)->toBe('¿De qué calibre lo necesitas?')
        ->and($respuesta->opciones)->toBe([
            ['etiqueta' => '3/8 pulgada', 'valor' => '3/8 pulgada'],
            ['etiqueta' => '1/2 pulgada', 'valor' => '1/2 pulgada'],
            ['etiqueta' => '3/4 pulgada', 'valor' => '3/4 pulgada'],
        ]);
});

test('envía al modelo el catálogo, las herramientas y el historial reciente', function () {
    Http::preventStrayRequests();
    $conversacion = Conversacion::factory()->create();
    Mensaje::factory()->for($conversacion)->create(['rol' => 'user', 'contenido' => 'Mensaje anterior']);
    Material::factory()->create(['sku' => 'ACE-002', 'nombre' => 'Varilla corrugada']);

    Http::fake([
        'api.deepseek.com/chat/completions' => Http::response(respuestaDeTexto('Va.')),
    ]);

    app(AgenteCompras::class)->responder($conversacion, 'Otro material');

    Http::assertSent(function (Request $peticion): bool {
        $sistema = $peticion['messages'][0]['content'] ?? '';

        return $peticion['model'] === 'deepseek-flash'
            && count($peticion['tools']) === 11
            && $peticion['tool_choice'] === 'auto'
            && $peticion['thinking'] === ['type' => 'disabled']
            && str_contains((string) $sistema, 'CATÁLOGO DISPONIBLE')
            && str_contains((string) $sistema, 'ACE-002')
            && str_contains((string) $sistema, 'OBRAS DISPONIBLES');
    });
});

test('no genera orden ni guarda respuesta cuando el servicio falla', function () {
    Http::preventStrayRequests();
    $conversacion = Conversacion::factory()->create();

    Http::fake([
        'api.deepseek.com/chat/completions' => Http::response(['error' => ['message' => 'Bad request']], 400),
    ]);

    expect(fn () => app(AgenteCompras::class)->responder($conversacion, 'Hola'))
        ->toThrow(DeepSeekException::class);

    expect($conversacion->mensajes()->where('rol', 'assistant')->count())->toBe(0)
        ->and(OrdenCompra::query()->whereNotNull('folio')->count())->toBe(0);
});

test('no llama al servicio cuando falta la clave de DeepSeek', function () {
    Http::preventStrayRequests();
    Http::fake();
    config(['services.deepseek.key' => null]);
    $conversacion = Conversacion::factory()->create();

    expect(fn () => app(AgenteCompras::class)->responder($conversacion, 'Hola'))
        ->toThrow(DeepSeekException::class, 'El agente no está configurado');

    Http::assertNothingSent();
});
