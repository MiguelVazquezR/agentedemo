<?php

use App\Enums\EstatusObra;
use App\Models\Conversacion;
use App\Models\Material;
use App\Models\Obra;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraItem;
use App\Models\Proveedor;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('muestra el resumen de compras de las obras', function () {
    $usuario = User::factory()->create();
    $obra = Obra::factory()->create([
        'codigo' => 'OB-001',
        'nombre' => 'Las Palmas',
        'estatus' => EstatusObra::EnEjecucion,
        'presupuesto_autorizado' => 1000000,
    ]);
    $proveedor = Proveedor::factory()->create();
    $material = Material::factory()->create(['precio_unitario' => 500]);
    $generada = OrdenCompra::factory()->generada()->create([
        'obra_id' => $obra->id,
        'proveedor_id' => $proveedor->id,
        'folio' => 'OC-2026-0001',
    ]);
    OrdenCompraItem::factory()->for($generada)->for($material)->create([
        'cantidad' => 2,
        'precio_unitario' => 500,
    ]);
    OrdenCompra::factory()->borrador()->create([
        'obra_id' => $obra->id,
        'proveedor_id' => $proveedor->id,
    ]);
    Conversacion::factory()->for($usuario)->create();

    $this->actingAs($usuario)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $pagina) => $pagina
            ->component('Dashboard')
            ->where('resumen.ordenes', 1)
            ->where('resumen.por_enviar', 1)
            ->where('resumen.monto', fn ($monto): bool => (float) $monto === 1160.0)
            ->where('resumen.materiales', 1)
            ->where('resumen.obras', 1)
            ->where('resumen.conversaciones', 1)
            ->has('ordenesRecientes', 1)
            ->where('ordenesRecientes.0.folio', 'OC-2026-0001')
            ->where('ordenesRecientes.0.partidas', 1)
            ->has('obras', 1)
            ->where('obras.0.codigo', 'OB-001')
            ->where('obras.0.ordenes', 1)
            ->where('obras.0.comprometido', fn ($monto): bool => (float) $monto === 1160.0));
});
