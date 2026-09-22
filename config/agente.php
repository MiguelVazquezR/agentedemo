<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Límites de ejecución del agente
    |--------------------------------------------------------------------------
    |
    | Valores pensados para respuestas rápidas y de bajo consumo de tokens:
    | el catálogo viaja en el prompt del sistema (cacheado por DeepSeek) y
    | las herramientas sólo se usan para buscar o modificar la lista.
    |
    */

    'max_pasos' => 6,

    'ventana_historial' => 12,

    'max_tokens' => 900,

    'temperatura' => 0.2,

    'catalogo' => [
        'max_materiales_en_prompt' => 260,
        'max_resultados_busqueda' => 12,
    ],

];
