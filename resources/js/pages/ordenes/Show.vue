<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Download, MailPlus, MessagesSquare, Send } from '@lucide/vue';
import { ref } from 'vue';
import AvisoDeGastos from '@/components/compras/AvisoDeGastos.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { fechaConHora, fechaCorta, moneda } from '@/lib/formato';
import { index as agenteIndex } from '@/routes/agente';
import {
    enviar as enviarOrden,
    index as ordenesIndex,
    pdf as descargarPdf,
} from '@/routes/ordenes';
import type { OrdenDetalle } from '@/types';

const props = defineProps<{
    orden: OrdenDetalle;
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

const dialogoEnvio = ref(false);
const comentario = ref('');
const copias = ref('');
const enviando = ref(false);

const abrirEnvio = (): void => {
    comentario.value = '';
    copias.value = '';
    dialogoEnvio.value = true;
};

const enviar = (): void => {
    enviando.value = true;

    router.post(
        enviarOrden.url({ orden: props.orden.id }),
        { comentario: comentario.value, cc: copias.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                dialogoEnvio.value = false;
            },
            onFinish: () => {
                enviando.value = false;
            },
        },
    );
};
</script>

<template>
    <Head :title="`Orden ${orden.folio ?? ''}`" />

    <div class="space-y-5 p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="space-y-1">
                <Link
                    :href="ordenesIndex.url()"
                    class="text-muted-foreground hover:text-foreground inline-flex items-center gap-1 text-xs"
                >
                    <ArrowLeft class="size-3.5" />
                    Todas las órdenes
                </Link>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-semibold">{{ orden.folio }}</h1>
                    <Badge variant="outline" :class="orden.estatus_clase">
                        {{ orden.estatus_etiqueta }}
                    </Badge>
                </div>
                <p class="text-muted-foreground text-sm">
                    {{ orden.obra ?? 'Sin obra asignada' }} ·
                    {{ orden.proveedor ?? 'Sin proveedor' }} ·
                    {{ orden.total_partidas }} partidas
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <Button v-if="orden.conversacion_id !== null" variant="outline" as-child>
                    <Link
                        :href="
                            agenteIndex.url({
                                query: { conversacion: orden.conversacion_id },
                            })
                        "
                    >
                        <MessagesSquare class="size-4" />
                        Ver conversación
                    </Link>
                </Button>
                <Button variant="outline" as-child>
                    <a :href="descargarPdf.url({ orden: orden.id })">
                        <Download class="size-4" />
                        Descargar PDF
                    </a>
                </Button>
                <Button :disabled="!orden.puede_enviarse || enviando" @click="abrirEnvio">
                    <Send class="size-4" />
                    Enviar al proveedor
                </Button>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="bg-card overflow-hidden rounded-xl border">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="bg-muted/40 text-muted-foreground text-[11px] uppercase"
                            >
                                <tr>
                                    <th class="px-4 py-2.5 text-left font-medium">SKU</th>
                                    <th class="px-4 py-2.5 text-left font-medium">Material</th>
                                    <th class="px-4 py-2.5 text-right font-medium">Cantidad</th>
                                    <th class="px-4 py-2.5 text-right font-medium">
                                        P. unitario
                                    </th>
                                    <th class="px-4 py-2.5 text-right font-medium">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="partida in orden.partidas"
                                    :key="partida.id"
                                    class="border-t"
                                >
                                    <td
                                        class="text-muted-foreground px-4 py-2.5 font-mono text-xs"
                                    >
                                        {{ partida.sku }}
                                    </td>
                                    <td class="px-4 py-2.5">
                                        {{ partida.descripcion }}
                                        <span
                                            v-if="partida.notas"
                                            class="text-muted-foreground block text-[11px]"
                                        >
                                            {{ partida.notas }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right">
                                        {{ partida.cantidad }} {{ partida.unidad_simbolo }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right">
                                        {{ moneda(partida.precio_unitario) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-medium">
                                        {{ moneda(partida.importe) }}
                                    </td>
                                </tr>
                                <tr v-if="orden.partidas.length === 0">
                                    <td
                                        colspan="5"
                                        class="text-muted-foreground px-4 py-8 text-center text-xs"
                                    >
                                        Esta orden no tiene partidas.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-muted/20 border-t p-4">
                        <div class="ml-auto w-full max-w-xs space-y-1.5 text-sm">
                            <div class="flex justify-between text-xs">
                                <span class="text-muted-foreground">Subtotal</span>
                                <span>{{ moneda(orden.subtotal) }}</span>
                            </div>
                            <div v-if="orden.descuento > 0" class="flex justify-between text-xs">
                                <span class="text-muted-foreground">Descuento</span>
                                <span>- {{ moneda(orden.descuento) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-muted-foreground">IVA 16%</span>
                                <span>{{ moneda(orden.iva) }}</span>
                            </div>
                            <div
                                class="flex justify-between border-t pt-1.5 text-base font-semibold"
                            >
                                <span>Total MXN</span>
                                <span>{{ moneda(orden.total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-card rounded-xl border p-4">
                    <h2 class="text-sm font-semibold">Envíos al proveedor</h2>
                    <p v-if="orden.envios.length === 0" class="text-muted-foreground mt-2 text-xs">
                        Todavía no se ha enviado esta orden. Usa «Enviar al proveedor» para
                        mandarla por correo con el PDF adjunto.
                    </p>
                    <ul v-else class="mt-3 space-y-3">
                        <li
                            v-for="envio in orden.envios"
                            :key="envio.id"
                            class="rounded-lg border p-3 text-xs"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <span class="inline-flex items-center gap-1.5 font-medium">
                                    <MailPlus class="text-muted-foreground size-3.5" />
                                    {{ envio.destinatario }}
                                </span>
                                <Badge
                                    variant="outline"
                                    :class="
                                        envio.exito
                                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300'
                                            : 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-300'
                                    "
                                >
                                    {{ envio.exito ? 'Enviado' : 'Falló' }}
                                </Badge>
                            </div>
                            <p class="text-muted-foreground mt-1">
                                {{ fechaConHora(envio.enviado_en) }}
                                <template v-if="envio.usuario"> · {{ envio.usuario }}</template>
                                <template v-if="envio.copias.length > 0">
                                    · Copia: {{ envio.copias.join(', ') }}
                                </template>
                            </p>
                            <p v-if="envio.mensaje" class="mt-1">«{{ envio.mensaje }}»</p>
                            <p v-if="envio.error" class="text-destructive mt-1">
                                {{ envio.error }}
                            </p>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-card rounded-xl border p-4 text-sm">
                    <h2 class="text-muted-foreground text-xs font-medium uppercase">Obra</h2>
                    <p class="mt-1 font-medium">{{ orden.obra ?? 'Sin asignar' }}</p>
                    <p v-if="orden.obra_codigo" class="text-muted-foreground text-xs">
                        {{ orden.obra_codigo }} · {{ orden.obra_cliente }}
                    </p>
                    <p v-if="orden.obra_ubicacion" class="text-muted-foreground text-xs">
                        {{ orden.obra_ubicacion }}
                    </p>
                    <p class="text-muted-foreground mt-2 text-xs">
                        Fecha requerida: {{ fechaCorta(orden.fecha_requerida) }}
                    </p>
                </div>

                <div class="bg-card rounded-xl border p-4 text-sm">
                    <h2 class="text-muted-foreground text-xs font-medium uppercase">Proveedor</h2>
                    <p class="mt-1 font-medium">{{ orden.proveedor ?? 'Sin asignar' }}</p>
                    <p v-if="orden.proveedor_contacto" class="text-muted-foreground text-xs">
                        {{ orden.proveedor_contacto }}
                    </p>
                    <p v-if="orden.proveedor_email" class="text-muted-foreground text-xs">
                        {{ orden.proveedor_email }}
                    </p>
                    <p v-if="orden.proveedor_ciudad" class="text-muted-foreground text-xs">
                        {{ orden.proveedor_ciudad }}
                    </p>
                    <p class="text-muted-foreground mt-2 text-xs">
                        Pago: {{ orden.condiciones_pago ?? 'Por definir' }}
                    </p>
                </div>

                <div class="bg-card rounded-xl border p-4 text-sm">
                    <h2 class="text-muted-foreground text-xs font-medium uppercase">Seguimiento</h2>
                    <p class="text-muted-foreground mt-1 text-xs">
                        Creada: {{ fechaConHora(orden.creada_en) }}
                    </p>
                    <p class="text-muted-foreground text-xs">
                        Generada: {{ fechaConHora(orden.generada_en) }}
                    </p>
                    <p class="text-muted-foreground text-xs">
                        Enviada: {{ fechaConHora(orden.enviada_en) }}
                    </p>
                </div>

                <AvisoDeGastos />
            </div>
        </div>
    </div>

    <Dialog v-model:open="dialogoEnvio">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Enviar la orden {{ orden.folio }}</DialogTitle>
                <DialogDescription>
                    Se enviará a {{ orden.proveedor_email ?? 'el correo del proveedor' }} con el
                    PDF adjunto.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-3">
                <div class="space-y-1.5">
                    <Label class="text-xs">Mensaje para el proveedor (opcional)</Label>
                    <Textarea
                        v-model="comentario"
                        rows="3"
                        placeholder="Favor de confirmar existencias y fecha de entrega."
                    />
                </div>
                <div class="space-y-1.5">
                    <Label class="text-xs">Con copia a (opcional)</Label>
                    <Input v-model="copias" placeholder="compras@constructora-demo.mx" />
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="dialogoEnvio = false">Cancelar</Button>
                <Button :disabled="enviando" @click="enviar">
                    <Send class="size-4" />
                    Enviar ahora
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
