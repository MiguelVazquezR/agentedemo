<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { CheckCircle2, ClipboardList, Info, Trash2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import BuscadorCatalogo from '@/components/agente/BuscadorCatalogo.vue';
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
import { update as actualizarBorrador } from '@/routes/agente/borrador';
import {
    destroy as quitarPartida,
    update as cambiarPartida,
} from '@/routes/agente/borrador/partidas';
import type { Borrador, ObraOpcion, ProveedorOpcion } from '@/types';

const props = defineProps<{
    borrador: Borrador;
    obras: ObraOpcion[];
    proveedores: ProveedorOpcion[];
    conversacionId: number;
}>();

const SIN_ASIGNAR = 'sin-asignar';

const moneda = (valor: number): string =>
    new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(valor);

const cantidades = ref<Record<number, number>>({});
const fecha = ref<string>('');

watch(
    () => props.borrador,
    (borrador) => {
        cantidades.value = Object.fromEntries(
            borrador.partidas.map((partida) => [partida.material_id, partida.cantidad]),
        );
        fecha.value = borrador.fecha_requerida ?? '';
    },
    { immediate: true, deep: true },
);

const opciones = { preserveScroll: true, preserveState: true };

/**
 * Campos de la lista que se pueden ajustar desde el panel.
 */
type AjustesDeLaLista = {
    obra_id?: number | null;
    proveedor_id?: number | null;
    fecha_requerida?: string | null;
};

const guardarDatos = (datos: AjustesDeLaLista): void => {
    router.patch(
        actualizarBorrador.url({ conversacion: props.conversacionId }),
        datos,
        opciones,
    );
};

const cambiarObra = (valor: string): void => {
    guardarDatos({ obra_id: valor === SIN_ASIGNAR ? null : Number(valor) });
};

const cambiarProveedor = (valor: string): void => {
    guardarDatos({ proveedor_id: valor === SIN_ASIGNAR ? null : Number(valor) });
};

const cambiarFecha = (): void => {
    guardarDatos({ fecha_requerida: fecha.value === '' ? null : fecha.value });
};

const guardarCantidad = (materialId: number): void => {
    router.patch(
        cambiarPartida.url({ conversacion: props.conversacionId }),
        { material_id: materialId, cantidad: cantidades.value[materialId] },
        opciones,
    );
};

const quitar = (materialId: number): void => {
    router.delete(quitarPartida.url({ conversacion: props.conversacionId }), {
        ...opciones,
        data: { material_id: materialId },
    });
};

const obraSeleccionada = computed(() =>
    props.borrador.obra_id === null ? SIN_ASIGNAR : String(props.borrador.obra_id),
);

const proveedorSeleccionado = computed(() =>
    props.borrador.proveedor_id === null
        ? SIN_ASIGNAR
        : String(props.borrador.proveedor_id),
);
</script>

<template>
    <div
        class="border-sidebar-border/70 dark:border-sidebar-border flex h-full flex-col border-l"
    >
        <div class="flex items-center justify-between gap-2 border-b px-4 py-3">
            <div class="flex items-center gap-2">
                <ClipboardList class="text-primary size-4" />
                <h2 class="text-sm font-semibold">Lista de la orden</h2>
            </div>
            <Badge variant="outline" :class="borrador.estatus_clase">
                {{ borrador.estatus_etiqueta }}
            </Badge>
        </div>

        <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-4">
            <div class="space-y-3">
                <div class="grid gap-1.5">
                    <Label class="text-xs">Obra donde se solicita</Label>
                    <Select
                        :model-value="obraSeleccionada"
                        @update:model-value="(valor) => cambiarObra(String(valor))"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Selecciona la obra" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="SIN_ASIGNAR">Sin asignar</SelectItem>
                            <SelectItem
                                v-for="obra in obras"
                                :key="obra.id"
                                :value="String(obra.id)"
                            >
                                {{ obra.codigo }} · {{ obra.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-1.5">
                    <Label class="text-xs">Proveedor</Label>
                    <Select
                        :model-value="proveedorSeleccionado"
                        @update:model-value="(valor) => cambiarProveedor(String(valor))"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Selecciona el proveedor" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="SIN_ASIGNAR">Sin asignar</SelectItem>
                            <SelectItem
                                v-for="proveedor in proveedores"
                                :key="proveedor.id"
                                :value="String(proveedor.id)"
                            >
                                {{ proveedor.nombre }} · {{ proveedor.condicion_pago }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-1.5">
                    <Label class="text-xs">Fecha requerida en obra</Label>
                    <Input v-model="fecha" type="date" @change="cambiarFecha" />
                </div>
            </div>

            <div class="space-y-2">
                <Label class="text-xs">
                    Materiales ({{ borrador.partidas.length }})
                </Label>

                <p
                    v-if="borrador.partidas.length === 0"
                    class="text-muted-foreground rounded-lg border border-dashed px-3 py-6 text-center text-xs"
                >
                    Aún no hay materiales. Pídeselos al agente o agrégalos del catálogo.
                </p>

                <div
                    v-for="partida in borrador.partidas"
                    :key="partida.material_id"
                    class="rounded-lg border p-2.5"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate text-xs font-medium">
                                {{ partida.descripcion }}
                            </p>
                            <p class="text-muted-foreground text-[11px]">
                                {{ partida.sku }} · {{ moneda(partida.precio_unitario) }} /
                                {{ partida.unidad_simbolo }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="text-muted-foreground hover:text-destructive shrink-0"
                            title="Quitar de la lista"
                            @click="quitar(partida.material_id)"
                        >
                            <Trash2 class="size-3.5" />
                            <span class="sr-only">Quitar de la lista</span>
                        </button>
                    </div>

                    <div class="mt-2 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <Input
                                v-model="cantidades[partida.material_id]"
                                type="number"
                                min="0.01"
                                step="0.01"
                                class="h-8 w-20"
                                @change="guardarCantidad(partida.material_id)"
                            />
                            <span class="text-muted-foreground text-[11px]">
                                {{ partida.unidad_simbolo }}
                            </span>
                        </div>
                        <span class="text-xs font-semibold">
                            {{ moneda(partida.importe) }}
                        </span>
                    </div>
                </div>

                <BuscadorCatalogo :conversacion-id="conversacionId" />
            </div>

            <div class="bg-muted/30 space-y-1.5 rounded-lg border p-3">
                <div class="flex justify-between text-xs">
                    <span class="text-muted-foreground">Subtotal</span>
                    <span>{{ moneda(borrador.subtotal) }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-muted-foreground">IVA 16%</span>
                    <span>{{ moneda(borrador.iva) }}</span>
                </div>
                <div class="flex justify-between border-t pt-1.5 text-sm font-semibold">
                    <span>Total</span>
                    <span>{{ moneda(borrador.total) }}</span>
                </div>
            </div>

            <div class="rounded-lg border p-3 text-xs">
                <div
                    v-if="borrador.faltantes.length === 0"
                    class="text-primary flex items-start gap-2"
                >
                    <CheckCircle2 class="mt-0.5 size-4 shrink-0" />
                    <span>La lista está completa y lista para generar la orden.</span>
                </div>
                <div v-else class="flex items-start gap-2">
                    <Info class="text-muted-foreground mt-0.5 size-4 shrink-0" />
                    <div class="space-y-1">
                        <p class="font-medium">Falta por definir:</p>
                        <ul class="text-muted-foreground ml-3 list-disc space-y-0.5">
                            <li v-for="faltante in borrador.faltantes" :key="faltante">
                                {{ faltante }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
