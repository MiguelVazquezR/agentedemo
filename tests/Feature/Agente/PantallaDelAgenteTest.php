<?php

use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Models\Obra;
use App\Models\Proveedor;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('muestra las conversaciones del usuario con la lista de la orden', function () {
    $usuario = User::factory()->create();
    $obra = Obra::factory()->create(['codigo' => 'OB-001', 'nombre' => 'Las Palmas']);
    Proveedor::factory()->create(['nombre' => 'Aceros del Norte']);
    Proveedor::factory()->inactivo()->create(['nombre' => 'Proveedor viejo']);

    $conversacion = Conversacion::factory()->for($usuario)->for($obra)->create(['titulo' => 'Varilla 3/8']);
    Mensaje::factory()->for($conversacion)->create(['rol' => 'user', 'contenido' => 'Necesito varilla']);
    Conversacion::factory()->create(['titulo' => 'De otro usuario']);

    $this->actingAs($usuario)
        ->get(route('agente.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $pagina) => $pagina
            ->component('agente/Index')
            ->has('conversaciones', 1)
            ->where('conversaciones.0.id', $conversacion->id)
            ->where('conversaciones.0.titulo', 'Varilla 3/8')
            ->where('conversaciones.0.obra', 'Las Palmas')
            ->where('conversacionActiva', $conversacion->id)
            ->has('mensajes', 1)
            ->where('mensajes.0.rol', 'user')
            ->where('mensajes.0.contenido', 'Necesito varilla')
            ->where('borrador.obra', 'Las Palmas')
            ->has('borrador.partidas', 0)
            ->where('borrador.estatus', 'borrador')
            ->has('obras', 1)
            ->where('obras.0.codigo', 'OB-001')
            ->has('proveedores', 1)
            ->where('proveedores.0.nombre', 'Aceros del Norte'));
});

test('abre la conversación que se pide en la URL', function () {
    $usuario = User::factory()->create();
    $reciente = Conversacion::factory()->for($usuario)->create(['ultimo_mensaje_at' => now()]);
    $antigua = Conversacion::factory()->for($usuario)->create(['ultimo_mensaje_at' => now()->subDay()]);
    Mensaje::factory()->for($antigua)->create(['rol' => 'user', 'contenido' => 'Mensaje viejo']);

    $this->actingAs($usuario)
        ->get(route('agente.index', ['conversacion' => $antigua->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $pagina) => $pagina
            ->where('conversacionActiva', $antigua->id)
            ->has('mensajes', 1)
            ->where('mensajes.0.contenido', 'Mensaje viejo'));

    expect($reciente->id)->not->toBe($antigua->id);
});

test('sin conversaciones la pantalla queda en blanco', function () {
    $usuario = User::factory()->create();

    $this->actingAs($usuario)
        ->get(route('agente.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $pagina) => $pagina
            ->component('agente/Index')
            ->has('conversaciones', 0)
            ->where('conversacionActiva', null)
            ->where('borrador', null)
            ->has('mensajes', 0));
});

test('abre una conversación nueva con el agente', function () {
    $usuario = User::factory()->create();

    $this->actingAs($usuario)->post(route('agente.store'))->assertRedirect();

    $conversacion = Conversacion::query()->sole();

    expect($conversacion->user_id)->toBe($usuario->id)
        ->and($conversacion->titulo)->toBe(Conversacion::TITULO_INICIAL);
});

test('elimina una conversación con sus mensajes', function () {
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();
    Mensaje::factory()->for($conversacion)->count(2)->create();

    $this->actingAs($usuario)
        ->delete(route('agente.destroy', $conversacion))
        ->assertRedirect(route('agente.index'));

    expect(Conversacion::query()->count())->toBe(0)
        ->and(Mensaje::query()->count())->toBe(0);
});

test('no permite abrir la conversación de otro usuario', function () {
    $ajeno = Conversacion::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('agente.index', ['conversacion' => $ajeno->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $pagina) => $pagina
            ->has('conversaciones', 0)
            ->where('conversacionActiva', null));

    $this->actingAs(User::factory()->create())
        ->post(route('agente.mensajes.store', $ajeno))
        ->assertNotFound();
});

test('las invitadas van al login', function () {
    $conversacion = Conversacion::factory()->create();

    $this->get(route('agente.index'))->assertRedirect(route('login'));
    $this->post(route('agente.mensajes.store', $conversacion))->assertRedirect(route('login'));
});
