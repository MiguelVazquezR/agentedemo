<?php

namespace App\Services\Agente;

use App\Enums\CategoriaMaterial;
use App\Enums\EstatusOrdenCompra;
use App\Models\Conversacion;
use App\Models\Material;
use App\Models\Obra;
use App\Models\OrdenCompra;
use App\Models\Proveedor;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Ejecuta las herramientas que el modelo solicita contra la lista real.
 */
class EjecutorDeHerramientas
{
    /**
     * @param  array<string, mixed>  $argumentos
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    public function ejecutar(string $herramienta, array $argumentos, Conversacion $conversacion, OrdenCompra $borrador): array
    {
        try {
            return match ($herramienta) {
                'buscar_materiales' => $this->buscarMateriales($argumentos),
                'listar_obras' => $this->listarObras(),
                'fijar_obra' => $this->fijarObra($argumentos, $conversacion, $borrador),
                'listar_proveedores' => $this->listarProveedores(),
                'fijar_proveedor' => $this->fijarProveedor($argumentos, $borrador),
                'fijar_fecha_requerida' => $this->fijarFechaRequerida($argumentos, $borrador),
                'agregar_a_lista' => $this->agregarALista($argumentos, $borrador),
                'cambiar_cantidad' => $this->cambiarCantidad($argumentos, $borrador),
                'quitar_de_lista' => $this->quitarDeLista($argumentos, $borrador),
                'ver_lista' => $this->verLista($borrador),
                'marcar_lista_terminada' => $this->marcarListaTerminada($borrador),
                default => $this->fallo("La herramienta {$herramienta} no existe."),
            };
        } catch (ModelNotFoundException) {
            return $this->fallo('No encontré ese registro: revisa el id o el SKU.');
        }
    }

    /**
     * @param  array<string, mixed>  $argumentos
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    private function buscarMateriales(array $argumentos): array
    {
        $termino = trim((string) ($argumentos['termino'] ?? ''));
        $categoria = CategoriaMaterial::tryFrom((string) ($argumentos['categoria'] ?? ''));
        $limite = (int) ($argumentos['limite'] ?? config('agente.catalogo.max_resultados_busqueda'));
        $limite = min(max($limite, 1), 30);

        $materiales = Material::query()
            ->where('activo', true)
            ->when($termino !== '', fn ($query) => $query->buscar($termino))
            ->when($categoria !== null, fn ($query) => $query->where('categoria', $categoria->value))
            ->orderBy('familia')
            ->orderBy('id')
            ->limit($limite)
            ->get();

        return $this->ok(
            [
                'coincidencias' => $materiales->count(),
                'materiales' => $materiales->map(fn (Material $material): array => $material->paraAgente())->all(),
            ],
            $termino === '' ? 'Consultando el catálogo' : "Buscando \"{$termino}\" en el catálogo",
        );
    }

    /**
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    private function listarObras(): array
    {
        $obras = Obra::query()->orderBy('codigo')->get();

        return $this->ok(
            [
                'obras' => $obras->map(fn (Obra $obra): array => [
                    'id' => $obra->id,
                    'codigo' => $obra->codigo,
                    'nombre' => $obra->nombre,
                    'cliente' => $obra->cliente,
                    'ubicacion' => $obra->ubicacion(),
                    'estatus' => $obra->estatus->label(),
                ])->all(),
            ],
            'Consultando las obras registradas',
        );
    }

    /**
     * @param  array<string, mixed>  $argumentos
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    private function fijarObra(array $argumentos, Conversacion $conversacion, OrdenCompra $borrador): array
    {
        $obra = Obra::query()->findOrFail((int) ($argumentos['obra_id'] ?? 0));

        $borrador->update(['obra_id' => $obra->id]);
        $conversacion->update(['obra_id' => $obra->id]);

        return $this->ok(
            [
                'obra' => [
                    'id' => $obra->id,
                    'codigo' => $obra->codigo,
                    'nombre' => $obra->nombre,
                    'cliente' => $obra->cliente,
                    'ubicacion' => $obra->ubicacion(),
                ],
            ],
            "Obra asignada: {$obra->nombre}",
        );
    }

    /**
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    private function listarProveedores(): array
    {
        $proveedores = Proveedor::query()->where('activo', true)->orderBy('nombre')->get();

        return $this->ok(
            [
                'proveedores' => $proveedores->map(fn (Proveedor $proveedor): array => [
                    'id' => $proveedor->id,
                    'nombre' => $proveedor->nombre,
                    'ciudad' => $proveedor->ciudad,
                    'condicion_pago' => $proveedor->condicionDePago(),
                    'email' => $proveedor->email,
                ])->all(),
            ],
            'Consultando los proveedores',
        );
    }

    /**
     * @param  array<string, mixed>  $argumentos
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    private function fijarProveedor(array $argumentos, OrdenCompra $borrador): array
    {
        $proveedor = Proveedor::query()->findOrFail((int) ($argumentos['proveedor_id'] ?? 0));

        $borrador->update([
            'proveedor_id' => $proveedor->id,
            'condiciones_pago' => $proveedor->condicionDePago(),
        ]);

        return $this->ok(
            [
                'proveedor' => [
                    'id' => $proveedor->id,
                    'nombre' => $proveedor->nombre,
                    'email' => $proveedor->email,
                    'condicion_pago' => $proveedor->condicionDePago(),
                ],
            ],
            "Proveedor asignado: {$proveedor->nombre}",
        );
    }

    /**
     * @param  array<string, mixed>  $argumentos
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    private function fijarFechaRequerida(array $argumentos, OrdenCompra $borrador): array
    {
        $fecha = trim((string) ($argumentos['fecha'] ?? ''));
        $requerida = $this->fechaValida($fecha);

        if (! $requerida instanceof CarbonImmutable) {
            return $this->fallo("La fecha \"{$fecha}\" no es válida. Usa el formato YYYY-MM-DD, por ejemplo 2026-10-15.");
        }

        $borrador->update(['fecha_requerida' => $requerida->toDateString()]);

        return $this->ok(
            ['fecha_requerida' => $requerida->toDateString()],
            'Fecha requerida: '.$requerida->isoFormat('D [de] MMMM [de] YYYY'),
        );
    }

    /**
     * Solo acepta fechas absolutas en formato YYYY-MM-DD.
     */
    private function fechaValida(string $fecha): ?CarbonImmutable
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) !== 1) {
            return null;
        }

        try {
            $requerida = CarbonImmutable::parse($fecha)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        return $requerida->toDateString() === $fecha ? $requerida : null;
    }

    /**
     * @param  array<string, mixed>  $argumentos
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    private function agregarALista(array $argumentos, OrdenCompra $borrador): array
    {
        $sku = strtoupper(trim((string) ($argumentos['sku'] ?? '')));
        $cantidad = (float) ($argumentos['cantidad'] ?? 0);

        if ($cantidad <= 0) {
            return $this->fallo('La cantidad debe ser mayor a cero.');
        }

        $material = $this->materialPorSku($sku);

        if ($material === null) {
            return $this->fallo("El SKU {$sku} no existe en el catálogo. Busca el material con buscar_materiales.");
        }

        $item = $borrador->agregarMaterial($material, $cantidad, $argumentos['notas'] ?? null);

        return $this->ok(
            [
                'partida' => $item->paraFrontend(),
                'subtotal' => (float) $borrador->refresh()->subtotal,
                'faltantes' => $borrador->pendientes(),
            ],
            'Agregado a la lista: '.$material->descripcionCompleta().' × '.$this->cantidad($item->cantidad),
        );
    }

    private function materialPorSku(string $sku): ?Material
    {
        return Material::query()->where('sku', $sku)->where('activo', true)->first();
    }

    private function cantidad(float|string $cantidad): string
    {
        return rtrim(rtrim(number_format((float) $cantidad, 2, '.', ','), '0'), '.');
    }

    /**
     * @param  array<string, mixed>  $argumentos
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    private function cambiarCantidad(array $argumentos, OrdenCompra $borrador): array
    {
        $sku = strtoupper(trim((string) ($argumentos['sku'] ?? '')));
        $cantidad = (float) ($argumentos['cantidad'] ?? 0);
        $material = $this->materialPorSku($sku);

        if ($material === null) {
            return $this->fallo("El SKU {$sku} no existe en el catálogo.");
        }

        if ($cantidad <= 0) {
            return $this->fallo('La cantidad debe ser mayor a cero. Si quieres eliminarla usa quitar_de_lista.');
        }

        if (! $borrador->actualizarCantidad($material->id, $cantidad)) {
            return $this->fallo("El material {$sku} todavía no está en la lista.");
        }

        return $this->ok(
            [
                'partida' => $borrador->items()->where('material_id', $material->id)->first()?->paraFrontend(),
                'total' => (float) $borrador->refresh()->total,
            ],
            'Cantidad actualizada: '.$material->descripcionCompleta().' × '.$this->cantidad($cantidad),
        );
    }

    /**
     * @param  array<string, mixed>  $argumentos
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    private function quitarDeLista(array $argumentos, OrdenCompra $borrador): array
    {
        $sku = strtoupper(trim((string) ($argumentos['sku'] ?? '')));
        $material = $this->materialPorSku($sku);

        if ($material === null) {
            return $this->fallo("El SKU {$sku} no existe en el catálogo.");
        }

        if (! $borrador->quitarMaterial($material->id)) {
            return $this->fallo("El material {$sku} no está en la lista.");
        }

        return $this->ok(
            [
                'subtotal' => (float) $borrador->refresh()->subtotal,
                'faltantes' => $borrador->pendientes(),
            ],
            'Quitado de la lista: '.$material->descripcionCompleta(),
        );
    }

    /**
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    private function verLista(OrdenCompra $borrador): array
    {
        $borrador->load('items.material');

        return $this->ok($borrador->resumenParaAgente(), 'Revisando la lista actual');
    }

    /**
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    private function marcarListaTerminada(OrdenCompra $borrador): array
    {
        $borrador->load('items.material');
        $pendientes = $borrador->pendientes();

        if ($pendientes !== []) {
            return $this->falloConDatos(
                ['lista_completa' => false, 'faltantes' => $pendientes],
                'Aún falta: '.implode(', ', $pendientes),
            );
        }

        $borrador->update(['estatus' => EstatusOrdenCompra::Completa]);

        return $this->ok(
            [
                'lista_completa' => true,
                'partidas' => $borrador->items->count(),
                'subtotal' => (float) $borrador->subtotal,
                'iva' => (float) $borrador->iva,
                'total' => (float) $borrador->total,
            ],
            'Lista lista para generar la orden: '.$borrador->items->count().' partidas',
        );
    }

    /**
     * @param  array<string, mixed>  $contenido
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    private function ok(array $contenido, string $resumen): array
    {
        return ['ok' => true, 'contenido' => [...$contenido, 'ok' => true], 'resumen' => $resumen];
    }

    /**
     * @param  array<string, mixed>  $contenido
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    private function falloConDatos(array $contenido, string $resumen): array
    {
        return ['ok' => false, 'contenido' => [...$contenido, 'ok' => false], 'resumen' => $resumen];
    }

    /**
     * @return array{ok: bool, contenido: array<string, mixed>, resumen: string}
     */
    private function fallo(string $mensaje): array
    {
        return $this->falloConDatos(['error' => $mensaje], $mensaje);
    }
}
