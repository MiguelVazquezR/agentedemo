/**
 * Formatea un importe en pesos mexicanos, por ejemplo $1,548.00.
 */
export function moneda(valor: number): string {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(valor);
}

/**
 * Fecha corta en español, por ejemplo 22 sep 2026.
 */
export function fechaCorta(valor: string | null): string {
    if (valor === null) {
        return '—';
    }

    return new Intl.DateTimeFormat('es-MX', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(valor));
}

/**
 * Fecha con hora, por ejemplo 22 sep 2026, 14:30.
 */
export function fechaConHora(valor: string | null): string {
    if (valor === null) {
        return '—';
    }

    return new Intl.DateTimeFormat('es-MX', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(valor));
}
