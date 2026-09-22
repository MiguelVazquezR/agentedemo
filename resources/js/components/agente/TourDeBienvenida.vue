<script setup lang="ts">
import { Sparkles } from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

/** Marca en el navegador para no repetir el tour. */
const CLAVE = 'agentedemo:agente:tour';

type Paso = {
    titulo: string;
    descripcion: string;
};

const pasos: Paso[] = [
    {
        titulo: 'Bienvenido a tu agente de compras',
        descripcion:
            'Descríbele con tus palabras el material que necesitas y arma contigo la lista de la orden de compra. No es un buscador: conversa, pregunta y calcula.',
    },
    {
        titulo: 'Tus conversaciones',
        descripcion:
            'En la columna izquierda tienes cada conversación con su propia lista. Abre una por obra, retómalas después o crea una nueva con el botón +.',
    },
    {
        titulo: 'Charla con el agente',
        descripcion:
            'Pídele en lenguaje natural, por ejemplo «40 tramos de varilla de 3/8». Te preguntará tipo, calibre, medida o color con las opciones que existen en el catálogo y tú eliges con un clic.',
    },
    {
        titulo: 'La lista de la orden',
        descripcion:
            'A la derecha se construye la lista. Ajusta cantidades, agrega material del catálogo o cambia la obra y el proveedor. Cuando esté completa podrás generar la orden, descargar el PDF y enviarla por correo.',
    },
];

const abierto = ref(false);
const indice = ref(0);

const paso = computed(() => pasos[indice.value]);
const esUltimo = computed(() => indice.value === pasos.length - 1);

const avanzar = (): void => {
    if (esUltimo.value) {
        cerrar();

        return;
    }

    indice.value += 1;
};

const cerrar = (): void => {
    abierto.value = false;
    localStorage.setItem(CLAVE, 'visto');
};

onMounted(() => {
    abierto.value = localStorage.getItem(CLAVE) === null;
});
</script>

<template>
    <Dialog :open="abierto" @update:open="(valor: boolean) => !valor && cerrar()">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <span
                    class="bg-primary/10 text-primary mb-2 flex size-10 items-center justify-center rounded-full"
                >
                    <Sparkles class="size-5" />
                </span>
                <DialogTitle>{{ paso.titulo }}</DialogTitle>
                <DialogDescription>{{ paso.descripcion }}</DialogDescription>
            </DialogHeader>

            <div class="flex items-center justify-between">
                <div class="flex gap-1.5">
                    <span
                        v-for="(_, posicion) in pasos"
                        :key="posicion"
                        class="h-1.5 rounded-full transition-all"
                        :class="
                            posicion === indice
                                ? 'bg-primary w-5'
                                : 'bg-muted-foreground/30 w-1.5'
                        "
                    />
                </div>
                <span class="text-muted-foreground text-xs">
                    Paso {{ indice + 1 }} de {{ pasos.length }}
                </span>
            </div>

            <DialogFooter>
                <Button variant="ghost" @click="cerrar">Saltar</Button>
                <Button @click="avanzar">
                    {{ esUltimo ? 'Empezar' : 'Siguiente' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
