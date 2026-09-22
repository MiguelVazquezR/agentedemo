<?php

use App\Enums\CategoriaMaterial;
use App\Enums\EstatusOrdenCompra;
use App\Models\Conversacion;
use App\Models\Material;
use App\Models\Obra;
use App\Models\OrdenCompra;
use App\Models\Proveedor;
use App\Services\Agente\EjecutorDeHerramientas;

beforeEach(function () {
    $this->conversacion = Conversacion::factory()->create();
    $this->borrador = OrdenCompra::factory()->for($this->conversacion)->create([
        'estatus' => EstatusOrdenCompra::Borrador,
        'obra_id' => null,
        'proveedor_id' => null,
        'fecha_requerida' => null,
    ]);
    $this->ejecutor = app(EjecutorDeHerramientas::class);
    $this->varilla = Material::factory()->create([
        'sku' => 'ACE-001',
        'nombre' => 'Varilla corrugada',
        'familia' => 'Varilla corrugada',
        'categoria' => CategoriaMaterial::Aceros,
        'medida' => '3/8" (9.5 mm)',
        'presentacion' => null,
        'color' => null,
        'marca' => null,
        'precio_unitario' => 129,
    ]);
});

test('agrega una partida con el precio del catálogo', function () {
    $resultado = $this->ejecutor->ejecutar('agregar_a_lista', ['sku' => 'ACE-001', 'cantidad' => 40], $this->conversacion, $this->borrador);

    expect($resultado['ok'])->toBeTrue()
        ->and($resultado['resumen'])->toBe('Agregado a la lista: Varilla corrugada · 3/8" (9.5 mm) × 40')
        ->and($this->borrador->refresh()->subtotal)->toBe('5160.00')
        ->and($this->borrador->iva)->toBe('825.60')
        ->and($this->borrador->total)->toBe('5985.60');
});

test('rechaza un SKU que no existe en el catálogo', function () {
    $resultado = $this->ejecutor->ejecutar('agregar_a_lista', ['sku' => 'XXX-999', 'cantidad' => 5], $this->conversacion, $this->borrador);

    expect($resultado['ok'])->toBeFalse()
        ->and($resultado['contenido']['error'])->toContain('XXX-999')
        ->and($this->borrador->items()->count())->toBe(0);
});

test('rechaza una cantidad menor o igual a cero', function () {
    $resultado = $this->ejecutor->ejecutar('agregar_a_lista', ['sku' => 'ACE-001', 'cantidad' => 0], $this->conversacion, $this->borrador);

    expect($resultado['ok'])->toBeFalse()
        ->and($this->borrador->items()->count())->toBe(0);
});

test('suma la cantidad cuando la partida ya está en la lista', function () {
    $this->ejecutor->ejecutar('agregar_a_lista', ['sku' => 'ACE-001', 'cantidad' => 10], $this->conversacion, $this->borrador);
    $this->ejecutor->ejecutar('agregar_a_lista', ['sku' => 'ACE-001', 'cantidad' => 5], $this->conversacion, $this->borrador);

    expect($this->borrador->items()->count())->toBe(1)
        ->and((float) $this->borrador->items()->firstOrFail()->cantidad)->toBe(15.0)
        ->and($this->borrador->refresh()->subtotal)->toBe('1935.00');
});

test('fija la cantidad exacta de una partida existente', function () {
    $this->ejecutor->ejecutar('agregar_a_lista', ['sku' => 'ACE-001', 'cantidad' => 10], $this->conversacion, $this->borrador);

    $resultado = $this->ejecutor->ejecutar('cambiar_cantidad', ['sku' => 'ACE-001', 'cantidad' => 25], $this->conversacion, $this->borrador);

    expect($resultado['ok'])->toBeTrue()
        ->and((float) $this->borrador->items()->firstOrFail()->cantidad)->toBe(25.0);
});

test('avisa cuando el material a corregir no está en la lista', function () {
    $resultado = $this->ejecutor->ejecutar('cambiar_cantidad', ['sku' => 'ACE-001', 'cantidad' => 25], $this->conversacion, $this->borrador);

    expect($resultado['ok'])->toBeFalse()
        ->and($resultado['contenido']['error'])->toContain('todavía no está en la lista');
});

test('quita una partida de la lista', function () {
    $this->ejecutor->ejecutar('agregar_a_lista', ['sku' => 'ACE-001', 'cantidad' => 10], $this->conversacion, $this->borrador);

    $resultado = $this->ejecutor->ejecutar('quitar_de_lista', ['sku' => 'ACE-001'], $this->conversacion, $this->borrador);

    expect($resultado['ok'])->toBeTrue()
        ->and($this->borrador->items()->count())->toBe(0)
        ->and($this->borrador->refresh()->subtotal)->toBe('0.00');
});

test('busca materiales por texto', function () {
    Material::factory()->create([
        'sku' => 'ELE-001',
        'nombre' => 'Cable THW calibre 12',
        'familia' => 'Cable THW',
        'medida' => 'Calibre 12',
        'categoria' => 'electrico',
    ]);

    $resultado = $this->ejecutor->ejecutar('buscar_materiales', ['termino' => 'varilla'], $this->conversacion, $this->borrador);

    expect($resultado['ok'])->toBeTrue()
        ->and($resultado['contenido']['coincidencias'])->toBe(1)
        ->and($resultado['contenido']['materiales'][0]['sku'])->toBe('ACE-001');
});

test('filtra la búsqueda por categoría', function () {
    Material::factory()->create([
        'sku' => 'ELE-001',
        'nombre' => 'Cable THW calibre 12',
        'familia' => 'Cable THW',
        'categoria' => 'electrico',
    ]);

    $resultado = $this->ejecutor->ejecutar('buscar_materiales', ['categoria' => 'electrico'], $this->conversacion, $this->borrador);

    expect($resultado['contenido']['coincidencias'])->toBe(1)
        ->and($resultado['contenido']['materiales'][0]['sku'])->toBe('ELE-001');
});

test('asigna la obra a la lista y a la conversación', function () {
    $obra = Obra::factory()->create(['nombre' => 'Nave Industrial Parque Norte']);

    $resultado = $this->ejecutor->ejecutar('fijar_obra', ['obra_id' => $obra->id], $this->conversacion, $this->borrador);

    expect($resultado['ok'])->toBeTrue()
        ->and($this->borrador->refresh()->obra_id)->toBe($obra->id)
        ->and($this->conversacion->refresh()->obra_id)->toBe($obra->id);
});

test('guarda el proveedor con su condición de pago', function () {
    $proveedor = Proveedor::factory()->create(['nombre' => 'Aceros del Norte', 'dias_credito' => 45]);

    $resultado = $this->ejecutor->ejecutar('fijar_proveedor', ['proveedor_id' => $proveedor->id], $this->conversacion, $this->borrador);

    expect($resultado['ok'])->toBeTrue()
        ->and($this->borrador->refresh()->proveedor_id)->toBe($proveedor->id)
        ->and($this->borrador->condiciones_pago)->toBe('Crédito 45 días');
});

test('registra la fecha requerida del material', function () {
    $resultado = $this->ejecutor->ejecutar('fijar_fecha_requerida', ['fecha' => '2026-10-15'], $this->conversacion, $this->borrador);

    expect($resultado['ok'])->toBeTrue()
        ->and($this->borrador->refresh()->fecha_requerida?->toDateString())->toBe('2026-10-15');
});

test('rechaza una fecha con formato inválido', function () {
    $resultado = $this->ejecutor->ejecutar('fijar_fecha_requerida', ['fecha' => 'la próxima semana'], $this->conversacion, $this->borrador);

    expect($resultado['ok'])->toBeFalse()
        ->and($resultado['contenido']['error'])->toContain('YYYY-MM-DD')
        ->and($this->borrador->refresh()->fecha_requerida)->toBeNull();
});

test('no cierra la lista cuando falta la obra o el proveedor', function () {
    $this->ejecutor->ejecutar('agregar_a_lista', ['sku' => 'ACE-001', 'cantidad' => 10], $this->conversacion, $this->borrador);

    $resultado = $this->ejecutor->ejecutar('marcar_lista_terminada', [], $this->conversacion, $this->borrador);

    expect($resultado['ok'])->toBeFalse()
        ->and($resultado['contenido']['faltantes'])->toHaveCount(2)
        ->and($this->borrador->refresh()->estatus)->toBe(EstatusOrdenCompra::Borrador);
});

test('cierra la lista cuando tiene obra, proveedor y materiales', function () {
    $this->borrador->update([
        'obra_id' => Obra::factory()->create()->id,
        'proveedor_id' => Proveedor::factory()->create()->id,
    ]);
    $this->ejecutor->ejecutar('agregar_a_lista', ['sku' => 'ACE-001', 'cantidad' => 10], $this->conversacion, $this->borrador);

    $resultado = $this->ejecutor->ejecutar('marcar_lista_terminada', [], $this->conversacion, $this->borrador);

    expect($resultado['ok'])->toBeTrue()
        ->and($resultado['contenido']['lista_completa'])->toBeTrue()
        ->and($this->borrador->refresh()->estatus)->toBe(EstatusOrdenCompra::Completa);
});

test('responde que la herramienta no existe', function () {
    $resultado = $this->ejecutor->ejecutar('borrar_todo', [], $this->conversacion, $this->borrador);

    expect($resultado['ok'])->toBeFalse()
        ->and($resultado['contenido']['error'])->toContain('borrar_todo');
});
