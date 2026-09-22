<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { MessagesSquare, Plus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import { destroy, index, store } from '@/routes/agente';
import type { ConversacionResumen } from '@/types';

defineProps<{
    conversaciones: ConversacionResumen[];
    activa: number | null;
}>();

const porEliminar = ref<ConversacionResumen | null>(null);
const eliminando = ref(false);

const abrirNueva = (): void => {
    router.post(store.url(), {}, { preserveScroll: true });
};

const eliminar = (): void => {
    if (porEliminar.value === null) {
        return;
    }

    eliminando.value = true;

    router.delete(destroy.url({ conversacion: porEliminar.value.id }), {
        onFinish: () => {
            eliminando.value = false;
            porEliminar.value = null;
        },
    });
};
</script>

<template>
    <div class="border-sidebar-border/70 dark:border-sidebar-border flex h-full flex-col border-r">
        <div class="flex items-center justify-between gap-2 border-b px-4 py-3">
            <div class="flex items-center gap-2">
                <MessagesSquare class="text-primary size-4" />
                <h2 class="text-sm font-semibold">Conversaciones</h2>
            </div>
            <Button
                size="icon-sm"
                variant="ghost"
                title="Nueva conversación"
                @click="abrirNueva"
            >
                <Plus class="size-4" />
                <span class="sr-only">Nueva conversación</span>
            </Button>
        </div>

        <div class="flex-1 overflow-y-auto p-2">
            <p
                v-if="conversaciones.length === 0"
                class="text-muted-foreground px-2 py-6 text-center text-xs"
            >
                Todavía no tienes conversaciones. Crea la primera con el botón +.
            </p>

            <Link
                v-for="conversacion in conversaciones"
                :key="conversacion.id"
                :href="index.url({ query: { conversacion: conversacion.id } })"
                preserve-scroll
                class="group hover:bg-accent/60 mb-1 flex items-start gap-2 rounded-lg px-2 py-2 text-left transition-colors"
                :class="
                    cn(
                        activa === conversacion.id &&
                            'bg-accent/70 border-primary/30 border',
                    )
                "
            >
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-xs font-medium">
                        {{ conversacion.titulo }}
                    </span>
                    <span class="text-muted-foreground block truncate text-[11px]">
                        {{ conversacion.obra ?? 'Sin obra asignada' }}
                        <template v-if="conversacion.ultimo_mensaje_at">
                            · {{ conversacion.ultimo_mensaje_at }}
                        </template>
                    </span>
                </span>

                <button
                    type="button"
                    class="text-muted-foreground hover:text-destructive shrink-0 opacity-0 transition-opacity group-hover:opacity-100"
                    title="Eliminar conversación"
                    @click.prevent.stop="porEliminar = conversacion"
                >
                    <Trash2 class="size-3.5" />
                    <span class="sr-only">Eliminar conversación</span>
                </button>
            </Link>
        </div>

        <Dialog
            :open="porEliminar !== null"
            @update:open="(abierto: boolean) => !abierto && (porEliminar = null)"
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>¿Eliminar la conversación?</DialogTitle>
                    <DialogDescription>
                        Se borrará «{{ porEliminar?.titulo }}» junto con sus mensajes y la lista
                        que no se haya convertido en orden de compra.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="outline">Cancelar</Button>
                    </DialogClose>
                    <Button variant="destructive" :disabled="eliminando" @click="eliminar">
                        Eliminar
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
