<?php

namespace App\Services\Agente;

use stdClass;

/**
 * Definiciones de las herramientas (function calling) que expone el agente.
 *
 * @see https://api-docs.deepseek.com/guides/tool_calls
 */
class HerramientasDelAgente
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function definiciones(): array
    {
        return [
            self::funcion(
                'buscar_materiales',
                'Busca materiales en el catálogo por texto libre, categoría o medida. Úsala cuando el usuario pida algo que quieras confirmar antes de agregar.',
                [
                    'termino' => ['type' => 'string', 'description' => 'Texto a buscar: nombre, marca, calibre, color o presentación.'],
                    'categoria' => ['type' => 'string', 'description' => 'Categoría del catálogo, por ejemplo cementos o aceros.'],
                    'limite' => ['type' => 'integer', 'description' => 'Máximo de resultados (por defecto 12).'],
                ],
            ),
            self::funcion(
                'listar_obras',
                'Devuelve las obras registradas con su id, cliente y estatus.',
                [],
            ),
            self::funcion(
                'fijar_obra',
                'Indica en qué obra se solicita el material. Obligatorio antes de cerrar la lista.',
                [
                    'obra_id' => ['type' => 'integer', 'description' => 'Id de la obra tomado de listar_obras.'],
                ],
                ['obra_id'],
            ),
            self::funcion(
                'listar_proveedores',
                'Devuelve los proveedores disponibles con su ciudad y condición de pago.',
                [],
            ),
            self::funcion(
                'fijar_proveedor',
                'Indica el proveedor al que se enviará la orden de compra.',
                [
                    'proveedor_id' => ['type' => 'integer', 'description' => 'Id del proveedor tomado de listar_proveedores.'],
                ],
                ['proveedor_id'],
            ),
            self::funcion(
                'fijar_fecha_requerida',
                'Registra la fecha en que se necesita el material en la obra.',
                [
                    'fecha' => ['type' => 'string', 'description' => 'Fecha en formato YYYY-MM-DD.'],
                ],
                ['fecha'],
            ),
            self::funcion(
                'agregar_a_lista',
                'Agrega un material del catálogo a la lista de la orden, con su SKU exacto y cantidad.',
                [
                    'sku' => ['type' => 'string', 'description' => 'SKU del catálogo, por ejemplo ACE-002.'],
                    'cantidad' => ['type' => 'number', 'description' => 'Cantidad solicitada en la unidad del material.'],
                    'notas' => ['type' => 'string', 'description' => 'Nota opcional para el proveedor sobre esta partida.'],
                ],
                ['sku', 'cantidad'],
            ),
            self::funcion(
                'cambiar_cantidad',
                'Corrige la cantidad de un material que ya está en la lista.',
                [
                    'sku' => ['type' => 'string', 'description' => 'SKU del material que ya está en la lista.'],
                    'cantidad' => ['type' => 'number', 'description' => 'Cantidad final que debe quedar en la lista.'],
                ],
                ['sku', 'cantidad'],
            ),
            self::funcion(
                'quitar_de_lista',
                'Elimina un material de la lista.',
                [
                    'sku' => ['type' => 'string', 'description' => 'SKU del material a eliminar.'],
                ],
                ['sku'],
            ),
            self::funcion(
                'ver_lista',
                'Devuelve la lista actual con partidas, subtotal, IVA y total, además de los datos que faltan por definir.',
                [],
            ),
            self::funcion(
                'marcar_lista_terminada',
                'Cierra la lista cuando ya tiene obra, proveedor y materiales. Si falta algo, devuelve lo que falta y no la cierra.',
                [],
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $propiedades
     * @param  array<int, string>  $requeridos
     * @return array<string, mixed>
     */
    private static function funcion(string $nombre, string $descripcion, array $propiedades, array $requeridos = []): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $nombre,
                'description' => $descripcion,
                'parameters' => [
                    'type' => 'object',
                    // La API exige un objeto JSON, no una lista vacía.
                    'properties' => $propiedades === [] ? new stdClass : $propiedades,
                    'required' => $requeridos,
                ],
            ],
        ];
    }
}
