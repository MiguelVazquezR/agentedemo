<script setup lang="ts">
import { Wallet } from '@lucide/vue';
</script>

<template>
    <div class="bg-muted/30 rounded-xl border border-dashed p-4">
        <div class="flex items-start gap-3">
            <span
                class="bg-background text-muted-foreground flex size-9 shrink-0 items-center justify-center rounded-full border"
            >
                <Wallet class="size-4" />
            </span>
            <div class="space-y-1">
                <p class="text-sm font-medium">
                    Opción de implementación: módulo de gastos
                </p>
                <p class="text-muted-foreground text-xs leading-relaxed">
                    Al autorizar una orden, su importe se registraría como compromiso de gasto
                    contra el presupuesto de la obra, con su CFDI y su pago programado en
                    tesorería. En esta demo el flujo termina cuando la orden se envía al
                    proveedor.
                </p>
            </div>
        </div>
    </div>
</template>
