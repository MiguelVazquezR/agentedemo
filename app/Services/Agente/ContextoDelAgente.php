<?php

namespace App\Services\Agente;

use App\Models\Conversacion;
use App\Models\Material;
use App\Models\Obra;
use App\Models\OrdenCompra;
use App\Models\Proveedor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Arma el prompt del sistema del agente.
 *
 * Las instrucciones y el catálogo van primero y son estables para que
 * DeepSeek pueda reutilizarlos desde su caché de contexto; el estado de la
 * conversación se agrega al final.
 */
class ContextoDelAgente
{
    public function sistema(Conversacion $conversacion, ?OrdenCompra $borrador): string
    {
        return implode("\n\n", [
            $this->instrucciones(),
            $this->catalogo(),
            $this->estado($conversacion, $borrador),
        ]);
    }

    private function instrucciones(): string
    {
        return <<<'TEXTO'
        Eres el asistente de compras de una empresa constructora en México. Ayudas al equipo a armar
        listas de material y órdenes de compra por obra.

        Reglas:
        - Responde siempre en español de México, en tono cordial y directo, máximo 4 líneas salvo que
          estés mostrando el resumen de la lista. Puedes usar **negritas** y listas con guiones.
        - Nunca inventes materiales, marcas ni precios: todo sale del CATÁLOGO de abajo.
        - Cuando falte un dato para agregar un material (tipo, calibre o medida, color, presentación o
          cantidad), pregunta antes de agregarlo y ofrece las opciones reales del catálogo.
        - Antes de agregar material, confirma la obra donde se solicitará. Si el usuario no la mencionó,
          pregunta cuál de las OBRAS DISPONIBLES es.
        - Usa las herramientas para modificar la lista; nunca digas que agregaste algo sin llamar a la
          herramienta. Cita el SKU exacto del catálogo.
        - Cuando la lista tenga obra, proveedor y al menos un material, llama a marcar_lista_terminada de
          inmediato (sin esperar a que el usuario lo pida) y avisa que ya puede generar la orden de compra.
        - Si el usuario pide algo que no está en el catálogo, ofrece la alternativa más cercana; no
          agregues artículos fuera del catálogo.
        - Cuando ofrezcas alternativas al usuario, termina tu respuesta con una línea exacta:
          OPCIONES: primera opción | segunda opción | tercera opción
          (máximo 4 opciones, de 2 a 5 palabras cada una).
        TEXTO;
    }

    /**
     * Índice compacto del catálogo agrupado por categoría y familia.
     * Se guarda como texto: el store de caché no acepta modelos Eloquent.
     */
    private function catalogo(): string
    {
        return Cache::remember($this->llaveDelCatalogo(), now()->addHour(), fn (): string => $this->construirCatalogo());
    }

    private function construirCatalogo(): string
    {
        $lineas = ['CATÁLOGO DISPONIBLE (precios en MXN por unidad, sin IVA):'];

        foreach ($this->materiales()->groupBy(fn (Material $material): string => $material->categoria->label()) as $categoria => $materiales) {
            $lineas[] = "# {$categoria}";

            foreach ($materiales->groupBy('familia') as $familia => $variantes) {
                $lineas[] = '- '.$this->cabeceraDeFamilia((string) $familia, $variantes).': '
                    .$variantes->map(fn (Material $material): string => $this->detalleDeVariante($material))->implode(' | ');
            }
        }

        return implode("\n", $lineas);
    }

    /**
     * Estado actual de la conversación, la obra y la lista.
     */
    private function estado(Conversacion $conversacion, ?OrdenCompra $borrador): string
    {
        $lineas = [
            'HOY: '.now()->isoFormat('dddd D [de] MMMM [de] YYYY'),
            'OBRAS DISPONIBLES:',
            ...$this->obras(),
            'PROVEEDORES DISPONIBLES (id = nombre, ciudad, condición):',
            ...$this->proveedores(),
        ];

        if ($conversacion->obra !== null) {
            $lineas[] = "OBRA DE ESTA CONVERSACIÓN: {$conversacion->obra->codigo} = {$conversacion->obra->nombre}";
        }

        $lineas[] = 'ESTADO ACTUAL DE LA LISTA: '
            .json_encode($borrador?->resumenParaAgente() ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return implode("\n", $lineas);
    }

    /**
     * @return array<int, string>
     */
    private function obras(): array
    {
        return Obra::query()
            ->orderBy('codigo')
            ->get()
            ->map(fn (Obra $obra): string => "- {$obra->id} = {$obra->codigo} {$obra->nombre} ({$obra->ubicacion()}, cliente: {$obra->cliente}, estatus: {$obra->estatus->label()})")
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function proveedores(): array
    {
        return Proveedor::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(fn (Proveedor $proveedor): string => "- {$proveedor->id} = {$proveedor->nombre}, {$proveedor->ciudad}, {$proveedor->condicionDePago()}")
            ->all();
    }

    /**
     * @param  Collection<int, Material>  $variantes
     */
    private function cabeceraDeFamilia(string $familia, Collection $variantes): string
    {
        /** @var Material $primero */
        $primero = $variantes->first();

        $atributos = array_filter([$primero->marca, 'por '.$primero->unidad->label()]);

        return $familia.' ('.implode(', ', $atributos).')';
    }

    private function detalleDeVariante(Material $material): string
    {
        $partes = array_filter([
            $material->sku,
            $material->medida,
            $material->presentacion,
            $material->color,
        ]);

        return implode(' · ', $partes).' · $'.number_format((float) $material->precio_unitario, 2);
    }

    /**
     * Catálogo activo que se envía al modelo.
     *
     * @return Collection<int, Material>
     */
    private function materiales(): Collection
    {
        return Material::query()
            ->where('activo', true)
            ->orderBy('categoria')
            ->orderBy('familia')
            ->orderBy('id')
            ->limit((int) config('agente.catalogo.max_materiales_en_prompt'))
            ->get();
    }

    /**
     * Llave de caché que cambia cuando cambia el catálogo.
     */
    private function llaveDelCatalogo(): string
    {
        $activos = Material::query()->where('activo', true);

        return 'agente:catalogo:'.md5((string) $activos->max('updated_at').$activos->count());
    }
}
