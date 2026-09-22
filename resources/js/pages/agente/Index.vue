<script setup lang="ts">
import { Head, router, useHttp } from '@inertiajs/vue3';
import { Sparkles } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import PanelBorrador from '@/components/agente/PanelBorrador.vue';
import PanelConversaciones from '@/components/agente/PanelConversaciones.vue';
import TourDeBienvenida from '@/components/agente/TourDeBienvenida.vue';
import VentanaChat from '@/components/agente/VentanaChat.vue';
import { Button } from '@/components/ui/button';
import { useMaquinaDeEscribir } from '@/composables/useMaquinaDeEscribir';
import { index as agenteIndex, store as abrirConversacion } from '@/routes/agente';
import { store as enviarMensaje } from '@/routes/agente/mensajes';
import type {
    Borrador,
    ConversacionResumen,
    MensajeChat,
    ObraOpcion,
    ProveedorOpcion,
    RespuestaAgente,
} from '@/types';

const props = defineProps<{
    conversaciones: ConversacionResumen[];
    conversacionActiva: number | null;
    mensajes: MensajeChat[];
    borrador: Borrador | null;
    obras: ObraOpcion[];
    proveedores: ProveedorOpcion[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Agente IA',
                href: agenteIndex.url(),
            },
        ],
    },
});

const mensajes = ref<MensajeChat[]>([...props.mensajes]);
const borrador = ref<Borrador | null>(props.borrador);
const escribiendoId = ref<number | null>(null);
const chat = ref<InstanceType<typeof VentanaChat> | null>(null);

const { texto: contenidoEnVivo, escribir, completar } = useMaquinaDeEscribir();

const formulario = useHttp<{ mensaje: string }, RespuestaAgente>({ mensaje: '' });

const conversacionId = computed(() => props.conversacionActiva);
const enviando = computed(() => formulario.processing);

const sugerencias = [
    'Necesito 40 tramos de varilla de 3/8 para la cimentación de Las Palmas',
    'Me faltan 30 sacos de cemento CPC 40 para la casa 3',
    'Necesito pintura e impermeabilizante para el corporativo',
];

watch(
    () => props.conversacionActiva,
    () => {
        completar();
        escribiendoId.value = null;
        mensajes.value = [...props.mensajes];
    },
);

watch(
    () => props.borrador,
    (nuevo) => {
        borrador.value = nuevo;
    },
);

const abrirNueva = (): void => {
    router.post(abrirConversacion.url(), {}, { preserveScroll: true });
};

const enviar = (texto: string): void => {
    const id = conversacionId.value;

    if (id === null || texto.trim() === '' || formulario.processing) {
        return;
    }

    const optimista: MensajeChat = {
        id: Date.now(),
        rol: 'user',
        contenido: texto,
        herramientas: [],
        opciones: [],
        creado_en: new Date().toISOString(),
    };

    formulario.mensaje = texto;
    mensajes.value = [...mensajes.value, optimista];

    void formulario.post(enviarMensaje.url({ conversacion: id }), {
        onSuccess: (respuesta) => {
            mensajes.value = [...mensajes.value, respuesta.mensaje];
            borrador.value = respuesta.borrador;
            formulario.mensaje = '';
            escribiendoId.value = respuesta.mensaje.id;
            escribir(respuesta.mensaje.contenido, () => {
                escribiendoId.value = null;
            });
        },
        onError: () => {
            mensajes.value = mensajes.value.filter((mensaje) => mensaje.id !== optimista.id);
            toast.error('El mensaje no puede estar vacío ni superar los 2000 caracteres.');
        },
        onHttpException: (respuesta) => {
            const detalle = (respuesta.data as { message?: string } | undefined)?.message;
            const mensaje = detalle ?? 'El agente no pudo responder en este momento.';

            toast.error(mensaje, { duration: 6000 });
            mensajes.value = [
                ...mensajes.value,
                {
                    id: Date.now() + 1,
                    rol: 'assistant',
                    contenido: `No pude responder: ${mensaje} Intenta de nuevo.`,
                    herramientas: [],
                    opciones: [{ etiqueta: 'Reintentar', valor: texto }],
                    creado_en: null,
                },
            ];
        },
    });
};
</script>

<template>
    <Head title="Agente IA" />

    <div class="flex h-[calc(100vh-4rem)] min-h-[540px] flex-col p-4">
        <div
            v-if="conversacionId === null"
            class="flex flex-1 items-center justify-center"
        >
            <div class="bg-card max-w-lg rounded-2xl border p-8 text-center shadow-sm">
                <span
                    class="bg-primary/10 text-primary mx-auto flex size-12 items-center justify-center rounded-full"
                >
                    <Sparkles class="size-6" />
                </span>
                <h1 class="mt-4 text-xl font-semibold">Tu agente de compras</h1>
                <p class="text-muted-foreground mt-2 text-sm">
                    Dile qué materiales necesitas para la obra y armamos juntos la lista de
                    la orden de compra. El agente te pregunta tipo, calibre, medida y color
                    con las opciones que existen en el mercado.
                </p>
                <Button class="mt-6" size="lg" @click="abrirNueva">
                    <Sparkles class="size-4" />
                    Iniciar con el agente
                </Button>
            </div>
        </div>

        <div
            v-else
            class="bg-background flex min-h-0 flex-1 overflow-hidden rounded-xl border shadow-xs"
        >
            <aside class="hidden w-64 shrink-0 xl:block">
                <PanelConversaciones
                    :conversaciones="conversaciones"
                    :activa="conversacionId"
                />
            </aside>

            <main class="min-w-0 flex-1">
                <VentanaChat
                    ref="chat"
                    :mensajes="mensajes"
                    :enviando="enviando"
                    :escribiendo-id="escribiendoId"
                    :contenido-en-vivo="contenidoEnVivo"
                    :sugerencias="sugerencias"
                    @enviar="enviar"
                />
            </main>

            <aside class="hidden w-80 shrink-0 lg:block">
                <PanelBorrador
                    v-if="borrador !== null"
                    :borrador="borrador"
                    :obras="obras"
                    :proveedores="proveedores"
                    :conversacion-id="conversacionId"
                />
            </aside>
        </div>

        <TourDeBienvenida />
    </div>
</template>
