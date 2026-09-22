<?php

use App\Enums\EstatusOrdenCompra;
use App\Models\Conversacion;
use App\Models\Material;
use App\Models\Obra;
use App\Models\OrdenCompra;
use App\Models\Proveedor;
use App\Models\User;
use App\Services\Agente\AgenteCompras;

test('genera la orden con folio a partir de la lista de la conversación', function () {
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();
    $obra = Obra::factory()->create(['nombre' => 'Las Palmas']);
    $proveedor = Proveedor::factory()->create(['nombre' => 'Aceros del Norte', 'dias_credito' => 15]);
    $material = Material::factory()->create(['sku' => 'ACE-001', 'precio_unitario' => 129]);

    $borrador = app(AgenteCompras::class)->borradorDe($conversacion);
    $borrador->agregarMaterial($material, 40);
    $borrador->refresh()->update([
        'obra_id' => $obra->id,
        'proveedor_id' => $proveedor->id,
        'condiciones_pago' => $proveedor->condicionDePago(),
    ]);

    $this->actingAs($usuario)
        ->post(route('agente.orden.store', $conversacion))
        ->assertRedirect(route('ordenes.show', ['orden' => $borrador->id]));

    $borrador->refresh();

    expect($borrador->folio)->toMatch('/^OC-\d{4}-\d{4}$/')
        ->and($borrador->estatus)->toBe(EstatusOrdenCompra::Generada)
        ->and($borrador->generada_at)->not->toBeNull()
        ->and((float) $borrador->total)->toBe(5985.6)
        ->and($borrador->condiciones_pago)->toBe('Crédito 15 días');
});

test('numera los folios de forma consecutiva', function () {
    OrdenCompra::factory()->create(['folio' => 'OC-2026-0007']);
    OrdenCompra::factory()->create(['folio' => 'OC-2025-0099']);

    expect(OrdenCompra::siguienteFolio(2026))->toBe('OC-2026-0008')
        ->and(OrdenCompra::siguienteFolio(2025))->toBe('OC-2025-0100');

    OrdenCompra::query()->delete();

    expect(OrdenCompra::siguienteFolio(2026))->toBe('OC-2026-0001');
});

test('no genera la orden si la lista está incompleta', function () {
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();

    $borrador = app(AgenteCompras::class)->borradorDe($conversacion);

    $this->actingAs($usuario)
        ->post(route('agente.orden.store', $conversacion))
        ->assertRedirect();

    $borrador->refresh();

    expect($borrador->folio)->toBeNull()
        ->and($borrador->estatus)->toBe(EstatusOrdenCompra::Borrador)
        ->and($borrador->generada_at)->toBeNull();
});

test('no permite generar dos veces la misma orden', function () {
    $usuario = User::factory()->create();
    $conversacion = Conversacion::factory()->for($usuario)->create();
    $material = Material::factory()->create(['precio_unitario' => 100]);

    $borrador = app(AgenteCompras::class)->borradorDe($conversacion);
    $borrador->agregarMaterial($material, 2);
    $borrador->refresh()->update([
        'obra_id' => Obra::factory()->create()->id,
        'proveedor_id' => Proveedor::factory()->create()->id,
    ]);

    $this->actingAs($usuario)->post(route('agente.orden.store', $conversacion));

    $folio = $borrador->refresh()->folio;

    $this->actingAs($usuario)
        ->post(route('agente.orden.store', $conversacion))
        ->assertRedirect();

    expect($borrador->refresh()->folio)->toBe($folio)
        ->and(OrdenCompra::query()->generadas()->count())->toBe(1);
});
