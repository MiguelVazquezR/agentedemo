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

export type OrdenResumen = {
    id: number;
    folio: string | null;
    estatus: string;
    estatus_etiqueta: string;
    estatus_clase: string;
    obra: string | null;
    obra_codigo: string | null;
    proveedor: string | null;
    fecha_requerida: string | null;
    partidas: number;
    total: number;
    generada_en: string | null;
    enviada_en: string | null;
};

export type EnvioDeOrden = {
    id: number;
    destinatario: string;
    copias: string[];
    asunto: string;
    mensaje: string | null;
    exito: boolean;
    error: string | null;
    usuario: string | null;
    enviado_en: string | null;
};

export type OrdenDetalle = Omit<OrdenResumen, 'partidas'> & {
    total_partidas: number;
    obra_id: number | null;
    obra_cliente: string | null;
    obra_ubicacion: string | null;
    proveedor_id: number | null;
    proveedor_contacto: string | null;
    proveedor_email: string | null;
    proveedor_ciudad: string | null;
    conversacion_id: number | null;
    condiciones_pago: string | null;
    observaciones: string | null;
    descuento: number;
    subtotal: number;
    iva: number;
    creada_en: string | null;
    partidas: PartidaBorrador[];
    envios: EnvioDeOrden[];
    puede_enviarse: boolean;
};

export type ResumenDeOrdenes = {
    cantidad: number;
    monto: number;
    por_enviar: number;
};

export type ObraResumen = {
    id: number;
    codigo: string;
    nombre: string;
    cliente: string;
    ubicacion: string;
    estatus: string;
    estatus_etiqueta: string;
    estatus_clase: string;
    presupuesto: number;
    ordenes: number;
    comprometido: number;
};

export type ResumenDelPanel = {
    ordenes: number;
    monto: number;
    por_enviar: number;
    materiales: number;
    obras: number;
    conversaciones: number;
};
