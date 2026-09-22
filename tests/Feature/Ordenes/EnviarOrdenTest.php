<?php

use App\Enums\EstatusOrdenCompra;
use App\Mail\OrdenCompraEnviada;
use App\Models\Material;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraItem;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('envía la orden al proveedor con el pdf adjunto', function () {
    Mail::fake();

    $usuario = User::factory()->create();
    $proveedor = Proveedor::factory()->create(['email' => 'ventas@aceros.test', 'dias_credito' => 30]);
    $material = Material::factory()->create(['precio_unitario' => 100]);
    $orden = OrdenCompra::factory()->generada()->create([
        'folio' => 'OC-2026-0009',
        'proveedor_id' => $proveedor->id,
    ]);
    OrdenCompraItem::factory()->for($orden)->for($material)->create(['cantidad' => 3, 'precio_unitario' => 100]);

    $this->actingAs($usuario)
        ->post(route('ordenes.enviar', $orden), [
            'comentario' => 'Favor de confirmar existencias.',
            'cc' => 'compras@constructora-demo.mx, correo-invalido',
        ])
        ->assertRedirect();

    Mail::assertSent(OrdenCompraEnviada::class, function (OrdenCompraEnviada $correo) use ($proveedor, $orden): bool {
        return $correo->hasTo($proveedor->email)
            && $correo->hasCc('compras@constructora-demo.mx')
            && $correo->orden->is($orden)
            && count($correo->attachments()) === 1
            && $correo->attachments()[0]->as === "orden-compra-{$orden->folio}.pdf";
    });

    $envio = $orden->envios()->sole();
    $orden->refresh();

    expect($envio->exito)->toBeTrue()
        ->and($envio->destinatario)->toBe('ventas@aceros.test')
        ->and($envio->copias())->toBe(['compras@constructora-demo.mx'])
        ->and($envio->asunto)->toBe('Orden de compra OC-2026-0009 · '.$orden->obra->nombre)
        ->and($envio->mensaje)->toBe('Favor de confirmar existencias.')
        ->and($envio->enviado_at)->not->toBeNull()
        ->and($envio->user_id)->toBe($usuario->id)
        ->and($orden->estatus)->toBe(EstatusOrdenCompra::Enviada)
        ->and($orden->enviada_at)->not->toBeNull();
});

test('registra el error cuando el proveedor no tiene correo', function () {
    Mail::fake();

    $usuario = User::factory()->create();
    $proveedor = Proveedor::factory()->create(['email' => '']);
    $orden = OrdenCompra::factory()->generada()->create(['proveedor_id' => $proveedor->id]);

    $this->actingAs($usuario)
        ->post(route('ordenes.enviar', $orden))
        ->assertRedirect();

    Mail::assertNothingSent();

    $envio = $orden->envios()->sole();

    expect($envio->exito)->toBeFalse()
        ->and($envio->error)->toBe('El proveedor no tiene correo registrado.')
        ->and($orden->refresh()->estatus)->toBe(EstatusOrdenCompra::Generada);
});

test('descarga el pdf de la orden', function () {
    $usuario = User::factory()->create();
    $material = Material::factory()->create(['precio_unitario' => 100]);
    $orden = OrdenCompra::factory()->generada()->create(['folio' => 'OC-2026-0010']);
    OrdenCompraItem::factory()->for($orden)->for($material)->create(['cantidad' => 3, 'precio_unitario' => 100]);

    $respuesta = $this->actingAs($usuario)->get(route('ordenes.pdf', $orden));

    $respuesta->assertOk()->assertHeader('content-type', 'application/pdf');

    expect($respuesta->getContent())->toStartWith('%PDF');
});

test('las invitadas no pueden enviar ni descargar la orden', function () {
    $orden = OrdenCompra::factory()->generada()->create();

    $this->post(route('ordenes.enviar', $orden))->assertRedirect(route('login'));
    $this->get(route('ordenes.pdf', $orden))->assertRedirect(route('login'));
});
