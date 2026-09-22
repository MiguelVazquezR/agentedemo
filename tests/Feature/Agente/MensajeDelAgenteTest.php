<?php

use App\Models\Conversacion;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('responde el mensaje y devuelve la lista actualizada', function () {
    Http::preventStrayRequests();
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();
    Material::factory()->create([
        'sku' => 'ACE-001',
        'nombre' => 'Varilla corrugada',
        'precio_unitario' => 129,
        'presentacion' => '12 m',
        'medida' => '3/8"',
    ]);

    Http::fake([
        'api.deepseek.com/chat/completions' => Http::sequence()
            ->push(respuestaDeHerramienta('agregar_a_lista', ['sku' => 'ACE-001', 'cantidad' => 40]))
            ->push(respuestaDeTexto('Agregué 40 tramos de varilla.')),
    ]);

    $respuesta = $this->actingAs($usuario)
        ->postJson(route('agente.mensajes.store', $conversacion), [
            'mensaje' => 'Necesito 40 tramos de varilla 3/8',
        ])
        ->assertOk()
        ->assertJsonPath('mensaje.rol', 'assistant')
        ->assertJsonPath('mensaje.contenido', 'Agregué 40 tramos de varilla.')
        ->assertJsonPath('mensaje.herramientas.0.nombre', 'agregar_a_lista')
        ->assertJsonPath('mensaje.herramientas.0.ok', true)
        ->assertJsonPath('borrador.partidas.0.sku', 'ACE-001')
        ->assertJsonPath('titulo', $conversacion->refresh()->titulo);

    $borrador = $respuesta->json('borrador');

    expect((float) $borrador['partidas'][0]['cantidad'])->toBe(40.0)
        ->and((float) $borrador['partidas'][0]['importe'])->toBe(5160.0)
        ->and((float) $borrador['subtotal'])->toBe(5160.0)
        ->and((float) $borrador['iva'])->toBe(825.6)
        ->and((float) $borrador['total'])->toBe(5985.6)
        ->and($borrador['lista_completa'])->toBeFalse()
        ->and($conversacion->mensajes()->count())->toBe(2);
});

test('exige el texto del mensaje', function () {
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();

    $this->actingAs($usuario)
        ->postJson(route('agente.mensajes.store', $conversacion), ['mensaje' => '  '])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('mensaje');

    expect($conversacion->mensajes()->count())->toBe(0);
});

test('avisa cuando el modelo no responde', function () {
    Http::preventStrayRequests();
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();

    Http::fake([
        'api.deepseek.com/chat/completions' => Http::response(
            ['error' => ['message' => 'Clave de API inválida']],
            400,
        ),
    ]);

    $this->actingAs($usuario)
        ->postJson(route('agente.mensajes.store', $conversacion), ['mensaje' => 'Necesito varilla'])
        ->assertStatus(503)
        ->assertJsonStructure(['message']);
});

test('no responde a la conversación de otro usuario', function () {
    Http::preventStrayRequests();
    Http::fake();

    $ajena = Conversacion::factory()->create();

    $this->actingAs(User::factory()->create())
        ->postJson(route('agente.mensajes.store', $ajena), ['mensaje' => 'Necesito varilla'])
        ->assertNotFound();
});
