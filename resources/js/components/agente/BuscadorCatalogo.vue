<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Loader2, Plus, Search } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { index as catalogo } from '@/routes/catalogo';
import { store as agregarPartida } from '@/routes/agente/borrador/partidas';
import type { MaterialCatalogo } from '@/types';

const props = defineProps<{
    conversacionId: number;
}>();

const abierto = ref(false);
const busqueda = ref('');
const cargando = ref(false);
const agregando = ref<number | null>(null);
const materiales = ref<MaterialCatalogo[]>([]);
const cantidades = ref<Record<number, number>>({});

let temporizador: ReturnType<typeof setTimeout> | null = null;

const buscar = async (): Promise<void> => {
    cargando.value = true;

    try {
        const respuesta = await fetch(
            `${catalogo.url()}?buscar=${encodeURIComponent(busqueda.value)}`,
            { headers: { Accept: 'application/json' } },
        );
        const datos = (await respuesta.json()) as { materiales?: MaterialCatalogo[] };

        materiales.value = datos.materiales ?? [];
    } finally {
        cargando.value = false;
    }
};

watch(busqueda, () => {
    if (temporizador !== null) {
        clearTimeout(temporizador);
    }

    temporizador = setTimeout(() => void buscar(), 250);
});

watch(abierto, (valor) => {
    if (valor && materiales.value.length === 0) {
        void buscar();
    }
});

const agregar = (material: MaterialCatalogo): void => {
    agregando.value = material.id;

    router.post(
        agregarPartida.url({ conversacion: props.conversacionId }),
        {
            material_id: material.id,
            cantidad: cantidades.value[material.id] ?? 1,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                agregando.value = null;
                abierto.value = false;
                busqueda.value = '';
            },
        },
    );
};

const precio = (valor: number): string =>
    new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(valor);
</script>

<template>
    <Dialog v-model:open="abierto">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm" class="w-full">
                <Plus class="size-4" />
                Agregar material del catálogo
            </Button>
        </DialogTrigger>

        <DialogContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Catálogo de materiales</DialogTitle>
                <DialogDescription>
                    Busca por nombre, calibre, color o marca. Solo se pueden agregar
                    materiales del catálogo.
                </DialogDescription>
            </DialogHeader>

            <div class="relative">
                <Search
                    class="text-muted-foreground pointer-events-none absolute top-2.5 left-3 size-4"
                />
                <Input
                    v-model="busqueda"
                    class="pl-9"
                    placeholder="Ej. varilla 3/8, impermeabilizante rojo, cable calibre 12"
                    autofocus
                />
                <Loader2
                    v-if="cargando"
                    class="text-muted-foreground absolute top-2.5 right-3 size-4 animate-spin"
                />
            </div>

            <div class="-mx-1 max-h-80 overflow-y-auto px-1">
                <p
                    v-if="!cargando && materiales.length === 0"
                    class="text-muted-foreground py-8 text-center text-sm"
                >
                    No encontré materiales con esa búsqueda.
                </p>

                <div
                    v-for="material in materiales"
                    :key="material.id"
                    class="hover:bg-accent/50 flex items-center gap-3 rounded-lg px-2 py-2"
                >
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium">{{ material.nombre }}</p>
                        <p class="text-muted-foreground truncate text-xs">
                            {{ material.descripcion }} · {{ material.sku }}
                        </p>
                        <p class="text-muted-foreground text-xs">
                            {{ precio(material.precio_unitario) }} /
                            {{ material.unidad_simbolo }}
                        </p>
                    </div>

                    <Input
                        v-model="cantidades[material.id]"
                        type="number"
                        min="0.01"
                        step="0.01"
                        class="h-8 w-20"
                        :default-value="1"
                    />

                    <Button
                        size="sm"
                        :disabled="agregando !== null"
                        @click="agregar(material)"
                    >
                        <Loader2
                            v-if="agregando === material.id"
                            class="size-4 animate-spin"
                        />
                        <Plus v-else class="size-4" />
                        Agregar
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
