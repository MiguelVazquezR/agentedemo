<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Datos fiscales de la empresa
    |--------------------------------------------------------------------------
    |
    | Se imprimen en el encabezado de la orden de compra en PDF y en el
    | correo que se envía al proveedor. Son datos de ejemplo de la demo.
    |
    */

    'empresa' => [
        'nombre' => 'Constructora Demo México',
        'razon_social' => 'Constructora Demo México, S.A. de C.V.',
        'rfc' => 'CDM240118H72',
        'direccion' => 'Av. Lázaro Cárdenas 1450, Col. Del Valle',
        'ciudad' => 'Monterrey, Nuevo León',
        'telefono' => '81 8123 4567',
        'email' => 'compras@constructora-demo.mx',
        'web' => 'constructora-demo.mx',
    ],

    /*
    |--------------------------------------------------------------------------
    | Reglas de cálculo de la orden de compra
    |--------------------------------------------------------------------------
    */

    'iva' => 0.16,

    'moneda' => 'MXN',

    'folio' => [
        'prefijo' => 'OC',
        'digitos' => 4,
    ],

];
