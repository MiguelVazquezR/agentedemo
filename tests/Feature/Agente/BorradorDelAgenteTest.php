<?php

use App\Enums\EstatusOrdenCompra;
use App\Models\Conversacion;
use App\Models\Material;
use App\Models\Obra;
use App\Models\Proveedor;
use App\Models\User;
use App\Services\Agente\AgenteCompras;

test('asigna obra, proveedor y fecha requerida a la lista', function () {
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();
    $obra = Obra::factory()->create(['nombre' => 'Las Palmas']);
    $proveedor = Proveedor::factory()->create(['nombre' => 'Aceros del Norte', 'dias_credito' => 30]);

    $this->actingAs($usuario)
        ->patch(route('agente.borrador.update', $conversacion), [
            'obra_id' => $obra->id,
            'proveedor_id' => $proveedor->id,
            'fecha_requerida' => '2026-10-05',
        ])
        ->assertRedirect();

    $borrador = app(AgenteCompras::class)->borradorDe($conversacion)->refresh();

    expect($borrador->obra_id)->toBe($obra->id)
        ->and($borrador->proveedor_id)->toBe($proveedor->id)
        ->and($borrador->condiciones_pago)->toBe('Crédito 30 días')
        ->and($borrador->fecha_requerida?->toDateString())->toBe('2026-10-05')
        ->and($borrador->estatus)->toBe(EstatusOrdenCompra::Borrador);
});

test('pide la fecha requerida en formato ISO', function () {
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();

    $this->actingAs($usuario)
        ->patch(route('agente.borrador.update', $conversacion), [
            'fecha_requerida' => 'la próxima semana',
        ])
        ->assertSessionHasErrors('fecha_requerida');
});

test('agrega una partida del catálogo y calcula los totales', function () {
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();
    $material = Material::factory()->create(['sku' => 'ACE-001', 'precio_unitario' => 129]);

    $this->actingAs($usuario)
        ->post(route('agente.borrador.partidas.store', $conversacion), [
            'material_id' => $material->id,
            'cantidad' => 12,
        ])
        ->assertRedirect();

    $borrador = app(AgenteCompras::class)->borradorDe($conversacion)->refresh();

    expect($borrador->items()->count())->toBe(1)
        ->and((float) $borrador->subtotal)->toBe(1548.0)
        ->and((float) $borrador->iva)->toBe(247.68)
        ->and((float) $borrador->total)->toBe(1795.68);
});

test('suma la cantidad cuando el material ya está en la lista', function () {
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();
    $material = Material::factory()->create(['precio_unitario' => 100]);

    $this->actingAs($usuario)->post(route('agente.borrador.partidas.store', $conversacion), [
        'material_id' => $material->id,
        'cantidad' => 5,
    ]);

    $this->actingAs($usuario)->post(route('agente.borrador.partidas.store', $conversacion), [
        'material_id' => $material->id,
        'cantidad' => 3,
    ]);

    $borrador = app(AgenteCompras::class)->borradorDe($conversacion)->refresh();

    expect($borrador->items()->count())->toBe(1)
        ->and((float) $borrador->items()->sole()->cantidad)->toBe(8.0);
});

test('no agrega materiales que no existen en el catálogo', function () {
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();

    $this->actingAs($usuario)
        ->post(route('agente.borrador.partidas.store', $conversacion), [
            'material_id' => 9999,
            'cantidad' => 1,
        ])
        ->assertSessionHasErrors('material_id');
});

test('corrige la cantidad de una partida', function () {
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();
    $material = Material::factory()->create(['precio_unitario' => 50]);

    $borrador = app(AgenteCompras::class)->borradorDe($conversacion);
    $borrador->agregarMaterial($material, 10);

    $this->actingAs($usuario)
        ->patch(route('agente.borrador.partidas.update', $conversacion), [
            'material_id' => $material->id,
            'cantidad' => 20,
        ])
        ->assertRedirect();

    expect((float) $borrador->refresh()->subtotal)->toBe(1000.0);
});

test('quita una partida de la lista', function () {
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();
    $material = Material::factory()->create(['precio_unitario' => 50]);

    $borrador = app(AgenteCompras::class)->borradorDe($conversacion);
    $borrador->agregarMaterial($material, 10);

    $this->actingAs($usuario)
        ->delete(route('agente.borrador.partidas.destroy', $conversacion), [
            'material_id' => $material->id,
        ])
        ->assertRedirect();

    expect($borrador->refresh()->items()->count())->toBe(0)
        ->and((float) $borrador->subtotal)->toBe(0.0);
});

test('marca la lista como completa cuando tiene obra, proveedor y materiales', function () {
    $usuario = User::factory()->create();
    $obra = Obra::factory()->create();
    $proveedor = Proveedor::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();
    $material = Material::factory()->create(['precio_unitario' => 100]);

    $borrador = app(AgenteCompras::class)->borradorDe($conversacion);
    $borrador->agregarMaterial($material, 2);
    $borrador->refresh()->update(['proveedor_id' => $proveedor->id]);

    $this->actingAs($usuario)
        ->patch(route('agente.borrador.update', $conversacion), ['obra_id' => $obra->id])
        ->assertRedirect();

    $borrador->refresh();

    expect($borrador->estatus)->toBe(EstatusOrdenCompra::Completa)
        ->and($borrador->puedeGenerarse())->toBeTrue()
        ->and(app(AgenteCompras::class)->estadoDelBorrador($conversacion)['faltantes'])->toBe([]);
});

test('busca materiales del catálogo para el panel', function () {
    $usuario = User::factory()->create();
    Material::factory()->create(['nombre' => 'Varilla corrugada', 'familia' => 'Varilla corrugada', 'sku' => 'ACE-001']);
    Material::factory()->create(['nombre' => 'Cemento CPC 40', 'familia' => 'Cemento CPC 40', 'sku' => 'CEM-001']);

    $this->actingAs($usuario)
        ->getJson(route('catalogo.index', ['buscar' => 'varilla']))
        ->assertOk()
        ->assertJsonCount(1, 'materiales')
        ->assertJsonPath('materiales.0.sku', 'ACE-001')
        ->assertJsonPath('materiales.0.id', Material::query()->where('sku', 'ACE-001')->sole()->id)
        ->assertJsonStructure([
            'materiales' => [['id', 'sku', 'nombre', 'descripcion', 'unidad', 'unidad_simbolo', 'precio_unitario']],
        ]);
});
