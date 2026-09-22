<script setup lang="ts">
import { Sparkles } from '@lucide/vue';
import { nextTick, ref, watch } from 'vue';
import BurbujaMensaje from '@/components/agente/BurbujaMensaje.vue';
import EntradaMensaje from '@/components/agente/EntradaMensaje.vue';
import type { MensajeChat } from '@/types';

const props = withDefaults(
    defineProps<{
        mensajes: MensajeChat[];
        enviando?: boolean;
        escribiendoId?: number | null;
        contenidoEnVivo?: string;
        sugerencias?: string[];
    }>(),
    {
        enviando: false,
        escribiendoId: null,
        contenidoEnVivo: '',
        sugerencias: () => [],
    },
);

const emit = defineEmits<{
    (e: 'enviar', texto: string): void;
}>();

const contenedor = ref<HTMLElement | null>(null);
const entrada = ref<InstanceType<typeof EntradaMensaje> | null>(null);

const desplazarAlFinal = (): void => {
    void nextTick(() => {
        const elemento = contenedor.value;

        if (elemento !== null) {
            elemento.scrollTop = elemento.scrollHeight;
        }
    });
};

watch(
    () => [props.mensajes.length, props.contenidoEnVivo, props.enviando],
    desplazarAlFinal,
    { immediate: true },
);

defineExpose({
    enfocar: () => entrada.value?.enfocar(),
    escribir: (texto: string) => entrada.value?.escribir(texto),
});
</script>

<template>
    <div class="flex h-full min-h-0 flex-col">
        <div ref="contenedor" class="min-h-0 flex-1 overflow-y-auto px-4 py-6">
            <div class="mx-auto max-w-3xl space-y-5">
                <div
                    v-if="mensajes.length === 0"
                    class="bg-card rounded-2xl border p-6 shadow-xs"
                >
                    <div class="flex items-center gap-2">
                        <span
                            class="bg-primary/10 text-primary flex size-9 items-center justify-center rounded-full"
                        >
                            <Sparkles class="size-4" />
                        </span>
                        <div>
                            <h2 class="text-base font-semibold">
                                Armemos la orden de compra
                            </h2>
                            <p class="text-muted-foreground text-xs">
                                Soy tu asistente de compras. Así funciona:
                            </p>
                        </div>
                    </div>

                    <ol class="mt-4 space-y-2 text-sm">
                        <li class="flex gap-2">
                            <span class="text-primary font-semibold">1.</span>
                            Dime qué material necesitas y para qué obra.
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary font-semibold">2.</span>
                            Te pregunto el tipo, calibre, medida o color con las opciones
                            que existen en el mercado.
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary font-semibold">3.</span>
                            Al terminar la lista puedes generar la orden, descargarla en
                            PDF o enviarla al proveedor por correo.
                        </li>
                    </ol>

                    <div v-if="sugerencias.length > 0" class="mt-5 space-y-2">
                        <p class="text-muted-foreground text-xs font-medium">
                            Prueba con una de estas:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="sugerencia in sugerencias"
                                :key="sugerencia"
                                type="button"
                                class="bg-background hover:bg-accent hover:text-accent-foreground hover:border-primary/40 rounded-full border px-3 py-1.5 text-left text-xs transition-colors"
                                @click="emit('enviar', sugerencia)"
                            >
                                {{ sugerencia }}
                            </button>
                        </div>
                    </div>
                </div>

                <BurbujaMensaje
                    v-for="mensaje in mensajes"
                    :key="mensaje.id"
                    :mensaje="mensaje"
                    :contenido-visible="
                        mensaje.id === escribiendoId ? contenidoEnVivo : undefined
                    "
                    :escribiendo="mensaje.id === escribiendoId"
                    @elegir="(valor: string) => emit('enviar', valor)"
                />

                <div v-if="enviando" class="flex items-center gap-3">
                    <span
                        class="bg-primary/10 text-primary flex size-8 items-center justify-center rounded-full"
                    >
                        <Sparkles class="size-4 animate-pulse" />
                    </span>
                    <span class="text-muted-foreground flex gap-1 text-xs">
                        Consultando el catálogo
                        <span class="animate-pulse">…</span>
                    </span>
                </div>
            </div>
        </div>

        <EntradaMensaje
            ref="entrada"
            :enviando="enviando"
            @enviar="(texto: string) => emit('enviar', texto)"
        />
    </div>
</template>
