import { onUnmounted, ref, type Ref } from 'vue';

type MaquinaDeEscribir = {
    texto: Ref<string>;
    escribiendo: Ref<boolean>;
    escribir: (contenido: string, alTerminar?: () => void) => void;
    completar: () => void;
};

/**
 * Muestra el texto letra por letra para dar sensación de respuesta en vivo.
 *
 * Avanza por bloques calculados sobre la longitud del texto, así una
 * respuesta corta se muestra casi al instante y una larga no se eterniza.
 */
export function useMaquinaDeEscribir(velocidad = 14): MaquinaDeEscribir {
    const texto = ref('');
    const escribiendo = ref(false);

    let temporizador: ReturnType<typeof setInterval> | null = null;
    let contenidoCompleto = '';
    let alTerminar: (() => void) | undefined;

    const detener = (): void => {
        if (temporizador !== null) {
            clearInterval(temporizador);
            temporizador = null;
        }
    };

    const completar = (): void => {
        detener();
        texto.value = contenidoCompleto;
        escribiendo.value = false;
        alTerminar?.();
    };

    const escribir = (contenido: string, callback?: () => void): void => {
        detener();

        contenidoCompleto = contenido;
        alTerminar = callback;
        texto.value = '';
        escribiendo.value = true;

        const paso = Math.max(1, Math.ceil(contenido.length / 180));

        temporizador = setInterval(() => {
            if (texto.value.length + paso >= contenidoCompleto.length) {
                completar();

                return;
            }

            texto.value = contenidoCompleto.slice(0, texto.value.length + paso);
        }, velocidad);
    };

    onUnmounted(detener);

    return { texto, escribiendo, escribir, completar };
}
