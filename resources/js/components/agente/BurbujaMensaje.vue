<script setup lang="ts">
import { Bot, Check, Sparkles, X } from '@lucide/vue';
import { computed } from 'vue';
import { renderizarMarkdown } from '@/lib/markdown';
import type { MensajeChat } from '@/types';

const props = defineProps<{
    mensaje: MensajeChat;
    contenidoVisible?: string;
    escribiendo?: boolean;
}>();

const emit = defineEmits<{
    (e: 'elegir', valor: string): void;
}>();

const esUsuario = computed(() => props.mensaje.rol === 'user');

const html = computed(() =>
    renderizarMarkdown(props.contenidoVisible ?? props.mensaje.contenido),
);
</script>

<template>
    <div class="flex gap-3" :class="esUsuario ? 'flex-row-reverse' : 'flex-row'">
        <div
            class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full border"
            :class="
                esUsuario
                    ? 'bg-background text-muted-foreground'
                    : 'bg-primary/10 text-primary border-primary/20'
            "
        >
            <component :is="esUsuario ? Check : Bot" class="size-4" />
        </div>

        <div class="flex max-w-[85%] flex-col gap-2">
            <div
                class="rounded-2xl px-4 py-2.5 text-sm leading-relaxed shadow-xs"
                :class="
                    esUsuario
                        ? 'bg-primary text-primary-foreground rounded-tr-sm'
                        : 'bg-muted/60 rounded-tl-sm border'
                "
            >
                <div
                    class="space-y-1 [&>p+ul]:mt-1.5 [&>p+p]:mt-1.5 [&>ul+p]:mt-1.5"
                    v-html="html"
                />
                <span
                    v-if="escribiendo"
                    class="ml-0.5 inline-block h-4 w-0.5 animate-pulse bg-current align-middle"
                />
            </div>

            <div
                v-if="!esUsuario && mensaje.herramientas.length > 0"
                class="flex flex-wrap gap-1.5"
            >
                <span
                    v-for="(paso, indice) in mensaje.herramientas"
                    :key="`${paso.nombre}-${indice}`"
                    class="text-muted-foreground bg-background inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px]"
                >
                    <Check v-if="paso.ok" class="text-primary size-3" />
                    <X v-else class="text-destructive size-3" />
                    {{ paso.detalle }}
                </span>
            </div>

            <div v-if="mensaje.opciones.length > 0 && !escribiendo" class="flex flex-wrap gap-1.5">
                <button
                    v-for="opcion in mensaje.opciones"
                    :key="opcion.valor"
                    type="button"
                    class="bg-background hover:bg-accent hover:text-accent-foreground hover:border-primary/40 inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors"
                    @click="emit('elegir', opcion.valor)"
                >
                    <Sparkles class="text-primary size-3" />
                    {{ opcion.etiqueta }}
                </button>
            </div>
        </div>
    </div>
</template>
