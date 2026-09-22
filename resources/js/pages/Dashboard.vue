<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, FileText, Package, Sparkles, Wallet } from '@lucide/vue';
import { computed } from 'vue';
import AvisoDeGastos from '@/components/compras/AvisoDeGastos.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { fechaCorta, moneda } from '@/lib/formato';
import { dashboard } from '@/routes';
import { index as agenteIndex } from '@/routes/agente';
import { index as ordenesIndex, show as mostrarOrden } from '@/routes/ordenes';
import type { ObraResumen, OrdenResumen, ResumenDelPanel } from '@/types';

const props = defineProps<{
    resumen: ResumenDelPanel;
    ordenesRecientes: OrdenResumen[];
    obras: ObraResumen[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Inicio',
                href: dashboard(),
            },
        ],
    },
});

const nombre = computed(() => usePage().props.auth.user.name.split(' ')[0]);

/**
 * Porcentaje del presupuesto de la obra ya comprometido en órdenes.
 */
const avance = (obra: ObraResumen): number => {
    if (obra.presupuesto <= 0) {
        return 0;
    }

    return Math.min(Math.round((obra.comprometido / obra.presupuesto) * 100), 100);
};

const tarjetas = computed(() => [
    {
        etiqueta: 'Órdenes generadas',
        valor: String(props.resumen.ordenes),
        detalle: `${props.resumen.por_enviar} pendientes de enviar`,
        icono: FileText,
    },
    {
        etiqueta: 'Monto comprometido',
        valor: moneda(props.resumen.monto),
        detalle: 'Suma de las órdenes generadas',
        icono: Wallet,
    },
    {
        etiqueta: 'Materiales en catálogo',
        valor: String(props.resumen.materiales),
        detalle: 'Precios de referencia vigentes',
        icono: Package,
    },
    {
        etiqueta: 'Obras y conversaciones',
        valor: `${props.resumen.obras} / ${props.resumen.conversaciones}`,
        detalle: 'Obras en ejecución / charlas con el agente',
        icono: Sparkles,
    },
]);
</script>

<template>
    <Head title="Inicio" />

    <div class="space-y-5 p-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Hola, {{ nombre }}</h1>
                <p class="text-muted-foreground text-sm">
                    Este es el resumen de compras de tus obras. El agente arma las órdenes
                    conversando contigo y tú las revisas antes de enviarlas.
                </p>
            </div>
            <Button as-child>
                <Link :href="agenteIndex.url()">
                    <Sparkles class="size-4" />
                    Armar una orden con el agente
                </Link>
            </Button>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div v-for="tarjeta in tarjetas" :key="tarjeta.etiqueta" class="bg-card rounded-xl border p-4">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-muted-foreground text-xs">{{ tarjeta.etiqueta }}</p>
                    <component :is="tarjeta.icono" class="text-muted-foreground size-4" />
                </div>
                <p class="mt-1 text-xl font-semibold">{{ tarjeta.valor }}</p>
                <p class="text-muted-foreground text-[11px]">{{ tarjeta.detalle }}</p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-5">
            <div class="bg-card overflow-hidden rounded-xl border lg:col-span-3">
                <div class="flex items-center justify-between gap-2 border-b px-4 py-3">
                    <h2 class="text-sm font-semibold">Órdenes recientes</h2>
                    <Link
                        :href="ordenesIndex.url()"
                        class="text-primary inline-flex items-center gap-1 text-xs hover:underline"
                    >
                        Ver todas
                        <ArrowRight class="size-3.5" />
                    </Link>
                </div>

                <p
                    v-if="ordenesRecientes.length === 0"
                    class="text-muted-foreground px-4 py-10 text-center text-xs"
                >
                    Todavía no hay órdenes generadas. Conversa con el agente para armar la
                    primera.
                </p>

                <table v-else class="w-full text-sm">
                    <tbody>
                        <tr
                            v-for="orden in ordenesRecientes"
                            :key="orden.id"
                            class="hover:bg-accent/40 border-t first:border-t-0"
                        >
                            <td class="px-4 py-3">
                                <Link
                                    :href="mostrarOrden.url({ orden: orden.id })"
                                    class="text-primary font-medium hover:underline"
                                >
                                    {{ orden.folio }}
                                </Link>
                                <span class="text-muted-foreground block text-[11px]">
                                    {{ orden.obra ?? 'Sin obra' }} · {{ orden.partidas }}
                                    partidas · {{ fechaCorta(orden.generada_en) }}
                                </span>
                            </td>
                            <td class="text-muted-foreground px-4 py-3 text-right text-xs">
                                {{ orden.proveedor ?? 'Sin proveedor' }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium">
                                {{ moneda(orden.total) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Badge variant="outline" :class="orden.estatus_clase">
                                    {{ orden.estatus_etiqueta }}
                                </Badge>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-card rounded-xl border p-4 lg:col-span-2">
                <h2 class="text-sm font-semibold">Tus obras</h2>
                <ul class="mt-3 space-y-4">
                    <li v-for="obra in obras" :key="obra.id">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ obra.nombre }}</p>
                                <p class="text-muted-foreground truncate text-[11px]">
                                    {{ obra.codigo }} · {{ obra.cliente }} ·
                                    {{ obra.ubicacion }}
                                </p>
                            </div>
                            <Badge variant="outline" :class="obra.estatus_clase">
                                {{ obra.estatus_etiqueta }}
                            </Badge>
                        </div>
                        <div class="bg-muted mt-2 h-1.5 w-full overflow-hidden rounded-full">
                            <div
                                class="bg-primary h-full rounded-full"
                                :style="{ width: `${avance(obra)}%` }"
                            />
                        </div>
                        <p class="text-muted-foreground mt-1 text-[11px]">
                            {{ moneda(obra.comprometido) }} comprometidos de
                            {{ moneda(obra.presupuesto) }} ·
                            {{ obra.ordenes }}
                            {{ obra.ordenes === 1 ? 'orden' : 'órdenes' }}
                        </p>
                    </li>
                </ul>
            </div>
        </div>

        <AvisoDeGastos />
    </div>
</template>
