export type RolMensaje = 'user' | 'assistant' | 'tool';

export type PasoHerramienta = {
    nombre: string;
    detalle: string;
    ok: boolean;
};

export type OpcionSugerida = {
    etiqueta: string;
    valor: string;
};

export type MensajeChat = {
    id: number;
    rol: RolMensaje;
    contenido: string;
    herramientas: PasoHerramienta[];
    opciones: OpcionSugerida[];
    creado_en: string | null;
};

export type PartidaBorrador = {
    id: number;
    material_id: number;
    sku: string;
    descripcion: string;
    unidad: string;
    unidad_simbolo: string;
    cantidad: number;
    precio_unitario: number;
    importe: number;
    notas: string | null;
};

export type Borrador = {
    id: number;
    folio: string | null;
    estatus: string;
    estatus_etiqueta: string;
    estatus_clase: string;
    obra_id: number | null;
    proveedor_id: number | null;
    obra: string | null;
    proveedor: string | null;
    fecha_requerida: string | null;
    subtotal: number;
    iva: number;
    total: number;
    partidas: PartidaBorrador[];
    faltantes: string[];
    lista_completa: boolean;
    puede_generarse: boolean;
};

export type ConversacionResumen = {
    id: number;
    titulo: string;
    obra: string | null;
    ultimo_mensaje_at: string | null;
};

export type ObraOpcion = {
    id: number;
    codigo: string;
    nombre: string;
    cliente: string;
    ubicacion: string;
};

export type ProveedorOpcion = {
    id: number;
    nombre: string;
    ciudad: string;
    condicion_pago: string;
};

export type MaterialCatalogo = {
    id: number;
    sku: string;
    nombre: string;
    marca: string | null;
    medida: string | null;
    presentacion: string | null;
    color: string | null;
    unidad: string;
    precio_unitario: number;
    descripcion: string;
    unidad_simbolo: string;
};

export type RespuestaAgente = {
    mensaje: MensajeChat;
    borrador: Borrador;
    titulo: string;
};
