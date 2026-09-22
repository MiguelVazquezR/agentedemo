<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus, Search } from '@lucide/vue';
import { computed, ref } from 'vue';
import AvisoDeGastos from '@/components/compras/AvisoDeGastos.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { fechaCorta, moneda } from '@/lib/formato';
import { index as agenteIndex } from '@/routes/agente';
import { index as ordenesIndex, show as mostrarOrden } from '@/routes/ordenes';
import type { OrdenResumen, ResumenDeOrdenes } from '@/types';

const props = defineProps<{
    ordenes: OrdenResumen[];
    resumen: ResumenDeOrdenes;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Órdenes de compra',
                href: ordenesIndex.url(),
            },
        ],
    },
});

const TODOS = 'todos';

const busqueda = ref('');
const estatus = ref(TODOS);

const estatusDisponibles = computed(() => {
    const encontrados = new Map<string, string>();

    props.ordenes.forEach((orden) => encontrados.set(orden.estatus, orden.estatus_etiqueta));

    return [...encontrados].map(([valor, etiqueta]) => ({ valor, etiqueta }));
});

const filtradas = computed(() => {
    const termino = busqueda.value.trim().toLowerCase();

    return props.ordenes.filter((orden) => {
        const coincideEstatus = estatus.value === TODOS || orden.estatus === estatus.value;
        const coincideTexto =
            termino === '' ||
            [orden.folio, orden.obra, orden.proveedor, orden.obra_codigo]
                .filter((valor): valor is string => typeof valor === 'string')
                .some((valor) => valor.toLowerCase().includes(termino));

        return coincideEstatus && coincideTexto;
    });
});

const montoFiltrado = computed(() =>
    filtradas.value.reduce((suma, orden) => suma + orden.total, 0),
);
</script>

<template>
    <Head title="Órdenes de compra" />

    <div class="space-y-5 p-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Órdenes de compra</h1>
                <p class="text-muted-foreground text-sm">
                    Órdenes generadas por el agente con su folio, PDF y envío al proveedor.
                </p>
            </div>
            <Button as-child>
                <Link :href="agenteIndex.url()">
                    <Plus class="size-4" />
                    Armar otra orden con el agente
                </Link>
            </Button>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="bg-card rounded-xl border p-4">
                <p class="text-muted-foreground text-xs">Órdenes generadas</p>
                <p class="mt-1 text-2xl font-semibold">{{ resumen.cantidad }}</p>
            </div>
            <div class="bg-card rounded-xl border p-4">
                <p class="text-muted-foreground text-xs">Monto comprometido</p>
                <p class="mt-1 text-2xl font-semibold">{{ moneda(resumen.monto) }}</p>
            </div>
            <div class="bg-card rounded-xl border p-4">
                <p class="text-muted-foreground text-xs">Pendientes de enviar</p>
                <p class="mt-1 text-2xl font-semibold">{{ resumen.por_enviar }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-[240px] flex-1 space-y-1.5">
                <Label class="text-xs">Buscar</Label>
                <div class="relative">
                    <Search
                        class="text-muted-foreground pointer-events-none absolute top-2.5 left-3 size-4"
                    />
                    <Input
                        v-model="busqueda"
                        class="pl-9"
                        placeholder="Folio, obra o proveedor"
                    />
                </div>
            </div>

            <div class="w-56 space-y-1.5">
                <Label class="text-xs">Estatus</Label>
                <Select v-model="estatus">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Todos" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="TODOS">Todas</SelectItem>
                        <SelectItem
                            v-for="opcion in estatusDisponibles"
                            :key="opcion.valor"
                            :value="opcion.valor"
                        >
                            {{ opcion.etiqueta }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <p class="text-muted-foreground pb-2 text-xs">
                {{ filtradas.length }} de {{ ordenes.length }} órdenes ·
                {{ moneda(montoFiltrado) }}
            </p>
        </div>

        <div class="bg-card overflow-hidden rounded-xl border">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-muted/40 text-muted-foreground text-[11px] uppercase">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-medium">Folio</th>
                            <th class="px-4 py-2.5 text-left font-medium">Obra</th>
                            <th class="px-4 py-2.5 text-left font-medium">Proveedor</th>
                            <th class="px-4 py-2.5 text-left font-medium">Requiere</th>
                            <th class="px-4 py-2.5 text-right font-medium">Partidas</th>
                            <th class="px-4 py-2.5 text-right font-medium">Total</th>
                            <th class="px-4 py-2.5 text-left font-medium">Estatus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="orden in filtradas"
                            :key="orden.id"
                            class="hover:bg-accent/40 border-t"
                        >
                            <td class="px-4 py-2.5">
                                <Link
                                    :href="mostrarOrden.url({ orden: orden.id })"
                                    class="text-primary font-medium hover:underline"
                                >
                                    {{ orden.folio }}
                                </Link>
                                <span class="text-muted-foreground block text-[11px]">
                                    {{ fechaCorta(orden.generada_en) }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5">
                                {{ orden.obra ?? 'Sin obra' }}
                                <span class="text-muted-foreground block text-[11px]">
                                    {{ orden.obra_codigo ?? '' }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5">
                                {{ orden.proveedor ?? 'Sin proveedor' }}
                            </td>
                            <td class="px-4 py-2.5">
                                {{ fechaCorta(orden.fecha_requerida) }}
                            </td>
                            <td class="px-4 py-2.5 text-right">{{ orden.partidas }}</td>
                            <td class="px-4 py-2.5 text-right font-medium">
                                {{ moneda(orden.total) }}
                            </td>
                            <td class="px-4 py-2.5">
                                <Badge variant="outline" :class="orden.estatus_clase">
                                    {{ orden.estatus_etiqueta }}
                                </Badge>
                            </td>
                        </tr>
                        <tr v-if="filtradas.length === 0">
                            <td
                                colspan="7"
                                class="text-muted-foreground px-4 py-10 text-center text-xs"
                            >
                                {{
                                    ordenes.length === 0
                                        ? 'Todavía no hay órdenes generadas. Conversa con el agente para armar la primera.'
                                        : 'Ninguna orden coincide con el filtro.'
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <AvisoDeGastos />
    </div>
</template>
