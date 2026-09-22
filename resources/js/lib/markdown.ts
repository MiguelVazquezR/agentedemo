const conNegritas = (texto: string): string =>
    texto.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

const escaparHtml = (texto: string): string =>
    texto
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');

/**
 * Renderiza un subconjunto de Markdown: negritas, listas y párrafos.
 *
 * El texto se escapa antes de aplicar el formato, por lo que el resultado
 * es seguro para usarse con v-html.
 */
export function renderizarMarkdown(texto: string): string {
    const bloques: string[] = [];
    let items: string[] = [];

    const cerrarLista = (): void => {
        if (items.length > 0) {
            bloques.push(`<ul class="ml-4 list-disc">${items.join('')}</ul>`);
            items = [];
        }
    };

    for (const linea of escaparHtml(texto).split('\n')) {
        const limpia = linea.trim();

        if (limpia.startsWith('- ') || limpia.startsWith('* ')) {
            items.push(`<li>${conNegritas(limpia.slice(2))}</li>`);
            continue;
        }

        cerrarLista();

        if (limpia !== '') {
            bloques.push(`<p>${conNegritas(limpia)}</p>`);
        }
    }

    cerrarLista();

    return bloques.join('');
}
