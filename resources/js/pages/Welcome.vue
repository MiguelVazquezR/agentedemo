<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    CheckCircle2,
    FileText,
    MessagesSquare,
    Package,
    Wallet,
} from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { dashboard, login } from '@/routes';
import type { Auth } from '@/types';

/** Sesión actual: la landing cambia su llamada a la acción si ya iniciaste sesión. */
const usuario = computed(() => (usePage().props as { auth?: Auth }).auth?.user ?? null);

const pasos = [
    {
        icono: MessagesSquare,
        titulo: 'Conversa con el agente',
        descripcion:
            'Pídele el material como lo dirías en la obra. Si falta el calibre, la medida, el color o el tipo, te pregunta con las opciones que existen en el catálogo.',
    },
    {
        icono: Package,
        titulo: 'Catálogo con precios reales',
        descripcion:
            'Más de 200 materiales con marca, medida, presentación y unidad. El agente solo puede usar lo que existe en el catálogo, así la lista siempre es comprable.',
    },
    {
        icono: FileText,
        titulo: 'Orden lista para enviar',
        descripcion:
            'Al cerrar la lista se asigna el folio, se calcula subtotal, IVA y total, se genera el PDF con los datos fiscales y se manda al proveedor por correo.',
    },
];

const cifras = [
    { valor: '212', etiqueta: 'materiales en catálogo' },
    { valor: '4', etiqueta: 'obras de ejemplo' },
    { valor: '6', etiqueta: 'proveedores' },
    { valor: 'OC-2026-0001', etiqueta: 'formato de folio' },
];
</script>

<template>
    <Head title="Agente de compras con IA">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-background text-foreground">
        <header class="border-b">
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 p-4">
                <div class="flex items-center gap-2">
                    <span
                        class="bg-primary text-primary-foreground flex size-8 items-center justify-center rounded-md text-sm font-bold"
                    >
                        IA
                    </span>
                    <span class="text-sm font-semibold">Agente de compras</span>
                </div>

                <div class="flex items-center gap-2">
                    <template v-if="usuario">
                        <Button as-child>
                            <Link :href="dashboard()">
                                Ir al panel
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                    </template>
                    <template v-else>
                        <Button variant="ghost" as-child>
                            <Link :href="login()">Iniciar sesión</Link>
                        </Button>
                        <Button as-child>
                            <Link :href="login()">
                                Entrar al demo
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                    </template>
                </div>
            </div>
        </header>

        <section class="mx-auto w-full max-w-6xl px-4 py-14 lg:py-20">
            <div class="grid items-center gap-10 lg:grid-cols-2">
                <div class="space-y-5">
                    <span
                        class="bg-primary/10 text-primary inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium"
                    >
                        Demo para constructoras en México
                    </span>
                    <h1 class="text-3xl leading-tight font-semibold lg:text-4xl">
                        De «necesito varilla para la cimentación» a la orden de compra firmada.
                    </h1>
                    <p class="text-muted-foreground text-base">
                        El agente entiende lo que pides en lenguaje natural, te pregunta lo que
                        falta con las opciones del catálogo y arma la orden completa: partidas,
                        precios, IVA, folio, PDF y correo al proveedor.
                    </p>

                    <ul class="space-y-2 text-sm">
                        <li class="flex items-start gap-2">
                            <CheckCircle2 class="text-primary mt-0.5 size-4 shrink-0" />
                            Sin capturar a mano: la lista se construye conversando.
                        </li>
                        <li class="flex items-start gap-2">
                            <CheckCircle2 class="text-primary mt-0.5 size-4 shrink-0" />
                            Precios del catálogo, con IVA y total calculados al momento.
                        </li>
                        <li class="flex items-start gap-2">
                            <CheckCircle2 class="text-primary mt-0.5 size-4 shrink-0" />
                            Orden con folio, PDF y envío por correo al proveedor.
                        </li>
                    </ul>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <Button size="lg" as-child>
                            <Link :href="usuario ? dashboard() : login()">
                                Ver el demo funcionando
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                        <Button size="lg" variant="outline" as-child>
                            <Link :href="login()">Entrar con el usuario demo</Link>
                        </Button>
                    </div>

                    <p class="text-muted-foreground text-xs">
                        Usuario demo: <span class="font-medium">demo@agentedemo.test</span> ·
                        contraseña <span class="font-medium">password</span>
                    </p>
                </div>
            </div>
        </section>

        <section class="border-t bg-muted/20">
            <div class="mx-auto w-full max-w-6xl px-4 py-14">
                <h2 class="text-xl font-semibold">Cómo funciona</h2>
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <div
                        v-for="paso in pasos"
                        :key="paso.titulo"
                        class="bg-card rounded-xl border p-5"
                    >
                        <span
                            class="bg-primary/10 text-primary flex size-9 items-center justify-center rounded-full"
                        >
                            <component :is="paso.icono" class="size-4" />
                        </span>
                        <h3 class="mt-3 text-sm font-semibold">{{ paso.titulo }}</h3>
                        <p class="text-muted-foreground mt-1.5 text-xs leading-relaxed">
                            {{ paso.descripcion }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto w-full max-w-6xl px-4 py-14">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="cifra in cifras"
                    :key="cifra.etiqueta"
                    class="rounded-xl border p-4"
                >
                    <p class="text-lg font-semibold">{{ cifra.valor }}</p>
                    <p class="text-muted-foreground text-xs">{{ cifra.etiqueta }}</p>
                </div>
            </div>

            <div class="bg-card mt-6 rounded-xl border border-dashed p-5">
                <div class="flex items-start gap-3">
                    <span
                        class="text-muted-foreground flex size-9 shrink-0 items-center justify-center rounded-full border"
                    >
                        <Wallet class="size-4" />
                    </span>
                    <div class="space-y-1">
                        <p class="text-sm font-medium">
                            Siguiente paso: módulo de gastos
                        </p>
                        <p class="text-muted-foreground text-xs leading-relaxed">
                            Al autorizar una orden, su importe se registraría como compromiso de
                            gasto contra el presupuesto de la obra, con su CFDI y su pago
                            programado en tesorería. Esta demo se detiene justo antes de ese
                            módulo.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t">
            <div
                class="text-muted-foreground mx-auto flex w-full max-w-6xl flex-wrap items-center justify-between gap-2 p-4 text-xs"
            >
                <span>Constructora Demo México · demo de compras asistidas por IA</span>
                <span>Datos de ejemplo, no incluye operación real</span>
            </div>
        </footer>
    </div>
</template>
