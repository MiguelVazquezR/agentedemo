<?php

use App\Models\Material;
use App\Models\Obra;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraItem;
use App\Models\Proveedor;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('lista las órdenes generadas con su resumen', function () {
    $usuario = User::factory()->create();
    $obra = Obra::factory()->create(['nombre' => 'Las Palmas', 'codigo' => 'OB-001']);
    $proveedor = Proveedor::factory()->create(['nombre' => 'Aceros del Norte']);
    $material = Material::factory()->create(['precio_unitario' => 500]);

    $generada = OrdenCompra::factory()->generada()->create([
        'obra_id' => $obra->id,
        'proveedor_id' => $proveedor->id,
        'folio' => 'OC-2026-0001',
    ]);
    OrdenCompraItem::factory()->for($generada)->for($material)->create(['cantidad' => 2, 'precio_unitario' => 500]);
    OrdenCompra::factory()->borrador()->create(['obra_id' => $obra->id, 'proveedor_id' => $proveedor->id]);

    $this->actingAs($usuario)
        ->get(route('ordenes.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $pagina) => $pagina
            ->component('ordenes/Index')
            ->has('ordenes', 1)
            ->where('ordenes.0.folio', 'OC-2026-0001')
            ->where('ordenes.0.obra', 'Las Palmas')
            ->where('ordenes.0.proveedor', 'Aceros del Norte')
            ->where('ordenes.0.partidas', 1)
            ->where('resumen.cantidad', 1)
            ->where('resumen.por_enviar', 1)
            ->where('resumen.monto', fn ($monto): bool => (float) $monto === 1160.0));
});

test('muestra el detalle de la orden con partidas y envíos', function () {
    $usuario = User::factory()->create();
    $material = Material::factory()->create(['sku' => 'ACE-001', 'nombre' => 'Varilla corrugada', 'precio_unitario' => 129]);

    $orden = OrdenCompra::factory()->generada()->create(['folio' => 'OC-2026-0005']);
    OrdenCompraItem::factory()->for($orden)->for($material)->create(['cantidad' => 40, 'precio_unitario' => 129]);

    $this->actingAs($usuario)
        ->get(route('ordenes.show', $orden))
        ->assertOk()
        ->assertInertia(fn (Assert $pagina) => $pagina
            ->component('ordenes/Show')
            ->where('orden.folio', 'OC-2026-0005')
            ->where('orden.estatus', 'generada')
            ->where('orden.puede_enviarse', true)
            ->has('orden.partidas', 1)
            ->where('orden.partidas.0.sku', 'ACE-001')
            ->where('orden.partidas.0.descripcion', fn (string $descripcion): bool => str_contains($descripcion, 'Varilla corrugada'))
            ->has('orden.envios', 0));
});

test('las invitadas van al login', function () {
    $orden = OrdenCompra::factory()->generada()->create();

    $this->get(route('ordenes.index'))->assertRedirect(route('login'));
    $this->get(route('ordenes.show', $orden))->assertRedirect(route('login'));
});
