<script setup lang="ts">
import { ArrowUp, Loader2 } from '@lucide/vue';
import { nextTick, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';

const props = withDefaults(
    defineProps<{
        enviando?: boolean;
        placeholder?: string;
    }>(),
    {
        enviando: false,
        placeholder: 'Escribe lo que necesitas, por ejemplo: 40 tramos de varilla de 3/8',
    },
);

const emit = defineEmits<{
    (e: 'enviar', texto: string): void;
}>();

const texto = ref('');
const campo = ref<HTMLTextAreaElement | null>(null);

const enviar = (): void => {
    const limpio = texto.value.trim();

    if (limpio === '' || props.enviando) {
        return;
    }

    emit('enviar', limpio);
    texto.value = '';

    void nextTick(() => campo.value?.focus());
};

const alPresionar = (evento: KeyboardEvent): void => {
    if (evento.key === 'Enter' && !evento.shiftKey) {
        evento.preventDefault();
        enviar();
    }
};

defineExpose({
    enfocar: () => campo.value?.focus(),
    escribir: (valor: string) => {
        texto.value = valor;
        campo.value?.focus();
    },
});
</script>

<template>
    <div class="bg-background/95 supports-[backdrop-filter]:bg-background/70 border-t backdrop-blur">
        <div class="mx-auto flex max-w-3xl items-end gap-2 p-3">
            <Textarea
                ref="campo"
                v-model="texto"
                :placeholder="placeholder"
                rows="1"
                class="max-h-40 min-h-[44px] resize-none"
                :disabled="enviando"
                @keydown="alPresionar"
            />

            <Button
                type="button"
                size="icon-lg"
                class="mb-0.5 rounded-full"
                :disabled="enviando || texto.trim() === ''"
                @click="enviar"
            >
                <Loader2 v-if="enviando" class="size-4 animate-spin" />
                <ArrowUp v-else class="size-4" />
                <span class="sr-only">Enviar mensaje</span>
            </Button>
        </div>

        <p class="text-muted-foreground px-4 pb-2 text-center text-[11px]">
            Enter envía · Shift + Enter hace un salto de línea
        </p>
    </div>
</template>
