<?php

namespace Database\Seeders;

use App\Enums\CategoriaMaterial;
use App\Enums\UnidadMedida;
use App\Models\Material;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Catálogo de materiales de construcción con precios de referencia en
     * pesos mexicanos. Son datos de demostración: se pueden ajustar
     * libremente desde esta misma estructura.
     */
    public function run(): void
    {
        $proveedores = Proveedor::query()->pluck('id', 'nombre');
        $contadores = [];

        foreach ($this->catalogo() as $familia) {
            $prefijo = $this->prefijos()[$familia['categoria']->value];

            foreach ($familia['variantes'] as $variante) {
                $contadores[$prefijo] = ($contadores[$prefijo] ?? 0) + 1;

                Material::query()->updateOrCreate(
                    ['sku' => sprintf('%s-%03d', $prefijo, $contadores[$prefijo])],
                    [
                        'nombre' => $familia['familia'],
                        'categoria' => $familia['categoria'],
                        'familia' => $familia['familia'],
                        'marca' => $variante['marca'] ?? $familia['marca'],
                        'unidad' => $variante['unidad'] ?? $familia['unidad'],
                        'presentacion' => $variante['presentacion'] ?? null,
                        'medida' => $variante['medida'] ?? null,
                        'color' => $variante['color'] ?? null,
                        'precio_unitario' => $variante['precio'],
                        'moneda' => 'MXN',
                        'especificaciones' => $variante['especificaciones'] ?? null,
                        'proveedor_preferido_id' => $proveedores[$familia['proveedor']] ?? null,
                        'activo' => true,
                    ],
                );
            }
        }
    }

    /**
     * Prefijo de SKU por categoría.
     *
     * @return array<string, string>
     */
    private function prefijos(): array
    {
        return [
            CategoriaMaterial::Cementos->value => 'CEM',
            CategoriaMaterial::Aceros->value => 'ACE',
            CategoriaMaterial::Agregados->value => 'AGR',
            CategoriaMaterial::Albanileria->value => 'ALB',
            CategoriaMaterial::Impermeabilizacion->value => 'IMP',
            CategoriaMaterial::Plomeria->value => 'PLO',
            CategoriaMaterial::Electrico->value => 'ELE',
            CategoriaMaterial::Acabados->value => 'ACA',
            CategoriaMaterial::Herramienta->value => 'HER',
            CategoriaMaterial::Seguridad->value => 'SEG',
        ];
    }

    /**
     * @return array<int, array{
     *     categoria: CategoriaMaterial,
     *     proveedor: string,
     *     familia: string,
     *     marca: string|null,
     *     unidad: UnidadMedida,
     *     variantes: array<int, array<string, mixed>>
     * }>
     */
    private function catalogo(): array
    {
        return [
            [
                'categoria' => CategoriaMaterial::Cementos,
                'proveedor' => 'Concretos y Cementos MX',
                'familia' => 'Cemento Portland CPC',
                'marca' => 'Cemex',
                'unidad' => UnidadMedida::Saco,
                'variantes' => [
                    ['medida' => 'CPC 30R', 'presentacion' => 'Saco 50 kg', 'precio' => 208.00],
                    ['medida' => 'CPC 40', 'presentacion' => 'Saco 50 kg', 'precio' => 219.00],
                    ['medida' => 'CPC 42.5R', 'presentacion' => 'Saco 50 kg', 'precio' => 236.00],
                    ['medida' => 'CPC 30R RS (sulfatos)', 'presentacion' => 'Saco 50 kg', 'precio' => 248.00],
                    ['medida' => 'CPC 30R Blanco', 'presentacion' => 'Saco 50 kg', 'precio' => 268.00],
                    ['medida' => 'CPC 30R', 'presentacion' => 'Saco 25 kg', 'precio' => 118.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Cementos,
                'proveedor' => 'Concretos y Cementos MX',
                'familia' => 'Concreto premezclado',
                'marca' => 'Concretos MX',
                'unidad' => UnidadMedida::MetroCubico,
                'variantes' => [
                    ['medida' => "f'c = 150 kg/cm²", 'presentacion' => 'Camión revolvedor', 'precio' => 2180.00],
                    ['medida' => "f'c = 200 kg/cm²", 'presentacion' => 'Camión revolvedor', 'precio' => 2320.00],
                    ['medida' => "f'c = 250 kg/cm²", 'presentacion' => 'Camión revolvedor', 'precio' => 2480.00],
                    ['medida' => "f'c = 300 kg/cm²", 'presentacion' => 'Camión revolvedor', 'precio' => 2690.00],
                    ['medida' => "f'c = 250 kg/cm²", 'presentacion' => 'Con bomba pluma', 'precio' => 3150.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Cementos,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Mortero para pegar block',
                'marca' => 'Cemex',
                'unidad' => UnidadMedida::Saco,
                'variantes' => [
                    ['presentacion' => 'Saco 50 kg', 'precio' => 169.00],
                    ['presentacion' => 'Saco 25 kg', 'precio' => 98.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Cementos,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Cal hidratada',
                'marca' => 'Calidra',
                'unidad' => UnidadMedida::Saco,
                'variantes' => [
                    ['presentacion' => 'Saco 25 kg', 'precio' => 96.00],
                    ['presentacion' => 'Saco 50 kg', 'precio' => 178.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Cementos,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Yeso de construcción',
                'marca' => 'Yesera Monterrey',
                'unidad' => UnidadMedida::Saco,
                'variantes' => [
                    ['presentacion' => 'Saco 40 kg', 'precio' => 152.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Cementos,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Mortero autonivelante',
                'marca' => 'Crest',
                'unidad' => UnidadMedida::Saco,
                'variantes' => [
                    ['medida' => 'Rápido (3 a 30 mm)', 'presentacion' => 'Saco 25 kg', 'precio' => 289.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Aceros,
                'proveedor' => 'Aceros del Norte',
                'familia' => 'Varilla corrugada grado 42',
                'marca' => 'Deacero',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '3/8" (9.5 mm)', 'presentacion' => 'Tramo 12 m', 'precio' => 129.00],
                    ['medida' => '1/2" (12.7 mm)', 'presentacion' => 'Tramo 12 m', 'precio' => 218.00],
                    ['medida' => '5/8" (15.9 mm)', 'presentacion' => 'Tramo 12 m', 'precio' => 342.00],
                    ['medida' => '3/4" (19.0 mm)', 'presentacion' => 'Tramo 12 m', 'precio' => 486.00],
                    ['medida' => '1" (25.4 mm)', 'presentacion' => 'Tramo 12 m', 'precio' => 838.00],
                    ['medida' => '3/8" (9.5 mm)', 'presentacion' => 'Tramo 6 m', 'precio' => 68.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Aceros,
                'proveedor' => 'Aceros del Norte',
                'familia' => 'Alambrón',
                'marca' => 'Deacero',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '1/4" (6.35 mm)', 'presentacion' => 'Tramo 12 m', 'precio' => 152.00],
                    ['medida' => '5/16" (7.9 mm)', 'presentacion' => 'Tramo 12 m', 'precio' => 208.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Aceros,
                'proveedor' => 'Aceros del Norte',
                'familia' => 'Alambre recocido',
                'marca' => 'Deacero',
                'unidad' => UnidadMedida::Rollo,
                'variantes' => [
                    ['medida' => 'Calibre 18', 'presentacion' => 'Rollo 1 kg', 'precio' => 46.00],
                    ['medida' => 'Calibre 18', 'presentacion' => 'Rollo 10 kg', 'precio' => 420.00],
                    ['medida' => 'Calibre 16', 'presentacion' => 'Rollo 10 kg', 'precio' => 445.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Aceros,
                'proveedor' => 'Aceros del Norte',
                'familia' => 'Malla electrosoldada',
                'marca' => 'Deacero',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '6x6 - 10/10', 'presentacion' => 'Panel 3.05 x 6 m', 'precio' => 1850.00],
                    ['medida' => '6x6 - 8/8', 'presentacion' => 'Panel 3.05 x 6 m', 'precio' => 2180.00],
                    ['medida' => '6x6 - 6/6', 'presentacion' => 'Panel 3.05 x 6 m', 'precio' => 2450.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Aceros,
                'proveedor' => 'Aceros del Norte',
                'familia' => 'Armex castillo',
                'marca' => 'Deacero',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '12 x 12 cm', 'presentacion' => 'Tramo 3 m', 'precio' => 168.00],
                    ['medida' => '15 x 15 cm', 'presentacion' => 'Tramo 3 m', 'precio' => 196.00],
                    ['medida' => '20 x 20 cm', 'presentacion' => 'Tramo 3 m', 'precio' => 245.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Aceros,
                'proveedor' => 'Aceros del Norte',
                'familia' => 'Alambre galvanizado',
                'marca' => 'Deacero',
                'unidad' => UnidadMedida::Rollo,
                'variantes' => [
                    ['medida' => 'Calibre 12', 'presentacion' => 'Rollo 100 m', 'precio' => 335.00],
                    ['medida' => 'Calibre 14', 'presentacion' => 'Rollo 100 m', 'precio' => 265.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Aceros,
                'proveedor' => 'Aceros del Norte',
                'familia' => 'Ángulo estructural',
                'marca' => 'Deacero',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '1 1/2" x 1/8"', 'presentacion' => 'Tramo 6 m', 'precio' => 325.00],
                    ['medida' => '2" x 1/8"', 'presentacion' => 'Tramo 6 m', 'precio' => 445.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Aceros,
                'proveedor' => 'Aceros del Norte',
                'familia' => 'PTR cuadrado',
                'marca' => 'Deacero',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '1" x 1" calibre 14', 'presentacion' => 'Tramo 6 m', 'precio' => 388.00],
                    ['medida' => '2" x 2" calibre 14', 'presentacion' => 'Tramo 6 m', 'precio' => 720.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Aceros,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Clavo',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Kilogramo,
                'variantes' => [
                    ['medida' => 'Para concreto 2 1/2"', 'precio' => 44.00],
                    ['medida' => 'Para madera 2"', 'precio' => 39.00],
                    ['medida' => 'Para techo 3"', 'precio' => 48.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Agregados,
                'proveedor' => 'Concretos y Cementos MX',
                'familia' => 'Arena',
                'marca' => null,
                'unidad' => UnidadMedida::MetroCubico,
                'variantes' => [
                    ['medida' => 'De río', 'presentacion' => 'Camión 7 m³', 'precio' => 480.00],
                    ['medida' => 'Triturada', 'presentacion' => 'Camión 7 m³', 'precio' => 455.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Agregados,
                'proveedor' => 'Concretos y Cementos MX',
                'familia' => 'Grava',
                'marca' => null,
                'unidad' => UnidadMedida::MetroCubico,
                'variantes' => [
                    ['medida' => '3/4"', 'presentacion' => 'Camión 7 m³', 'precio' => 520.00],
                    ['medida' => '1/2"', 'presentacion' => 'Camión 7 m³', 'precio' => 510.00],
                    ['medida' => 'Lavada para concreto', 'presentacion' => 'Camión 7 m³', 'precio' => 545.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Agregados,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Piedra bola',
                'marca' => null,
                'unidad' => UnidadMedida::MetroCubico,
                'variantes' => [
                    ['medida' => 'Para cimentación', 'presentacion' => 'Camión 7 m³', 'precio' => 620.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Agregados,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Tepetate',
                'marca' => null,
                'unidad' => UnidadMedida::MetroCubico,
                'variantes' => [
                    ['medida' => 'Para relleno', 'presentacion' => 'Camión 7 m³', 'precio' => 340.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Agregados,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Polvo de piedra',
                'marca' => null,
                'unidad' => UnidadMedida::MetroCubico,
                'variantes' => [
                    ['medida' => 'Criba fina', 'presentacion' => 'Camión 7 m³', 'precio' => 380.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Albanileria,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Block de concreto',
                'marca' => 'Blockera del Norte',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '12 x 20 x 40 cm', 'precio' => 14.50],
                    ['medida' => '15 x 20 x 40 cm', 'precio' => 17.50],
                    ['medida' => '20 x 20 x 40 cm', 'precio' => 21.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Albanileria,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Tabique rojo recocido',
                'marca' => null,
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '6 x 12 x 24 cm', 'precio' => 7.80],
                    ['medida' => 'Barro hueco 12 x 12 x 24 cm', 'precio' => 12.50],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Albanileria,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Vigueta pretensada',
                'marca' => 'Pretensados MX',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => 'Perfil 12 cm', 'presentacion' => 'Tramo 5 m', 'precio' => 385.00],
                    ['medida' => 'Perfil 15 cm', 'presentacion' => 'Tramo 6 m', 'precio' => 465.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Albanileria,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Bovedilla',
                'marca' => 'Pretensados MX',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => 'Poliestireno 15 cm', 'precio' => 42.00],
                    ['medida' => 'Concreto 15 cm', 'precio' => 68.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Albanileria,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Casetón de poliestireno',
                'marca' => 'Styroblock',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '15 cm', 'presentacion' => '60 x 60 cm', 'precio' => 55.00],
                    ['medida' => '20 cm', 'presentacion' => '60 x 60 cm', 'precio' => 68.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Albanileria,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Panel de yeso (tablaroca)',
                'marca' => 'USG',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => 'Estándar 9.5 mm', 'presentacion' => '1.22 x 2.44 m', 'precio' => 185.00],
                    ['medida' => 'Resistente a humedad', 'presentacion' => '1.22 x 2.44 m', 'precio' => 265.00],
                    ['medida' => 'Resistente al fuego', 'presentacion' => '1.22 x 2.44 m', 'precio' => 285.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Albanileria,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Poste galvanizado',
                'marca' => 'USG',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => 'Calibre 20', 'presentacion' => 'Tramo 3.05 m', 'precio' => 95.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Albanileria,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Canal galvanizado',
                'marca' => 'USG',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => 'Calibre 22', 'presentacion' => 'Tramo 3.05 m', 'precio' => 88.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Albanileria,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Compuesto para juntas',
                'marca' => 'USG',
                'unidad' => UnidadMedida::Cubeta,
                'variantes' => [
                    ['medida' => 'Listo para usar', 'presentacion' => 'Cubeta 4.5 L', 'precio' => 195.00],
                    ['medida' => 'Listo para usar', 'presentacion' => 'Cubeta 17 L', 'precio' => 585.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Impermeabilizacion,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Impermeabilizante acrílico 5 años',
                'marca' => 'Eureka',
                'unidad' => UnidadMedida::Cubeta,
                'variantes' => [
                    ['color' => 'Rojo óxido', 'presentacion' => 'Cubeta 19 L', 'precio' => 1890.00],
                    ['color' => 'Terracota', 'presentacion' => 'Cubeta 19 L', 'precio' => 1890.00],
                    ['color' => 'Blanco', 'presentacion' => 'Cubeta 19 L', 'precio' => 1890.00],
                    ['color' => 'Gris', 'presentacion' => 'Cubeta 19 L', 'precio' => 1890.00],
                    ['color' => 'Verde', 'presentacion' => 'Cubeta 19 L', 'precio' => 1890.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Impermeabilizacion,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Impermeabilizante acrílico 7 años',
                'marca' => 'Eureka',
                'unidad' => UnidadMedida::Cubeta,
                'variantes' => [
                    ['color' => 'Rojo óxido', 'presentacion' => 'Cubeta 19 L', 'precio' => 2480.00],
                    ['color' => 'Blanco', 'presentacion' => 'Cubeta 19 L', 'precio' => 2480.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Impermeabilizacion,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Impermeabilizante acrílico 3 años',
                'marca' => 'Fester',
                'unidad' => UnidadMedida::Cubeta,
                'variantes' => [
                    ['color' => 'Blanco', 'presentacion' => 'Cubeta 19 L', 'precio' => 1490.00],
                    ['color' => 'Rojo óxido', 'presentacion' => 'Cubeta 19 L', 'precio' => 1490.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Impermeabilizacion,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Sellador 5x1',
                'marca' => 'Comex',
                'unidad' => UnidadMedida::Cubeta,
                'variantes' => [
                    ['presentacion' => 'Cubeta 19 L', 'precio' => 1250.00],
                    ['presentacion' => 'Bote 4 L', 'precio' => 385.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Impermeabilizacion,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Impermeabilizante prefabricado (membrana)',
                'marca' => 'Membranas MX',
                'unidad' => UnidadMedida::Rollo,
                'variantes' => [
                    ['medida' => '3.5 mm', 'presentacion' => 'Rollo 10 m²', 'precio' => 1950.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Impermeabilizacion,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Primario asfáltico',
                'marca' => 'Fester',
                'unidad' => UnidadMedida::Cubeta,
                'variantes' => [
                    ['presentacion' => 'Cubeta 19 L', 'precio' => 1080.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Impermeabilizacion,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Cemento plástico',
                'marca' => 'Comex',
                'unidad' => UnidadMedida::Cubeta,
                'variantes' => [
                    ['presentacion' => 'Bote 4 L', 'precio' => 320.00],
                    ['presentacion' => 'Cubeta 19 L', 'precio' => 1050.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Impermeabilizacion,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Espuma de poliuretano expansiva',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['presentacion' => 'Bote 750 ml', 'precio' => 285.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Tubo PVC hidráulico',
                'marca' => 'Pavco',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '13 mm (1/2")', 'presentacion' => 'Tramo 6 m', 'precio' => 88.00],
                    ['medida' => '20 mm (3/4")', 'presentacion' => 'Tramo 6 m', 'precio' => 108.00],
                    ['medida' => '25 mm (1")', 'presentacion' => 'Tramo 6 m', 'precio' => 148.00],
                    ['medida' => '32 mm (1 1/4")', 'presentacion' => 'Tramo 6 m', 'precio' => 212.00],
                    ['medida' => '40 mm (1 1/2")', 'presentacion' => 'Tramo 6 m', 'precio' => 325.00],
                    ['medida' => '50 mm (2")', 'presentacion' => 'Tramo 6 m', 'precio' => 425.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Tubo PVC sanitario',
                'marca' => 'Pavco',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '40 mm (1 1/2")', 'presentacion' => 'Tramo 6 m', 'precio' => 148.00],
                    ['medida' => '50 mm (2")', 'presentacion' => 'Tramo 6 m', 'precio' => 188.00],
                    ['medida' => '75 mm (3")', 'presentacion' => 'Tramo 6 m', 'precio' => 315.00],
                    ['medida' => '100 mm (4")', 'presentacion' => 'Tramo 6 m', 'precio' => 528.00],
                    ['medida' => '150 mm (6")', 'presentacion' => 'Tramo 6 m', 'precio' => 985.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Tubo CPVC',
                'marca' => 'Flowguard',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '1/2"', 'presentacion' => 'Tramo 6 m', 'precio' => 148.00],
                    ['medida' => '3/4"', 'presentacion' => 'Tramo 6 m', 'precio' => 218.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Tubo de cobre',
                'marca' => 'Nacobre',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '1/2" tipo M', 'presentacion' => 'Tramo 6 m', 'precio' => 485.00],
                    ['medida' => '3/4" tipo M', 'presentacion' => 'Tramo 6 m', 'precio' => 690.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Codo 90° PVC hidráulico',
                'marca' => 'Pavco',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '13 mm', 'precio' => 12.00],
                    ['medida' => '20 mm', 'precio' => 16.00],
                    ['medida' => '25 mm', 'precio' => 24.00],
                    ['medida' => '32 mm', 'precio' => 38.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Tee PVC sanitaria',
                'marca' => 'Pavco',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '50 mm', 'precio' => 32.00],
                    ['medida' => '100 mm', 'precio' => 68.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Cople PVC hidráulico',
                'marca' => 'Pavco',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '20 mm', 'precio' => 15.00],
                    ['medida' => '25 mm', 'precio' => 22.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Pegamento para PVC',
                'marca' => 'Oatey',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['presentacion' => 'Bote 250 ml', 'precio' => 95.00],
                    ['presentacion' => 'Bote 1 L', 'precio' => 265.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Llave de paso esférica',
                'marca' => 'Urrea',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '1/2"', 'precio' => 158.00],
                    ['medida' => '3/4"', 'precio' => 225.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Válvula de llenado para tinaco',
                'marca' => 'Rotoplas',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '1/2"', 'precio' => 168.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Tinaco',
                'marca' => 'Rotoplas',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '750 litros', 'precio' => 3290.00],
                    ['medida' => '1100 litros', 'precio' => 4650.00],
                    ['medida' => '2500 litros', 'precio' => 8950.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Bomba centrífuga',
                'marca' => 'Evans',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '1 HP', 'presentacion' => 'Succión 1 1/4"', 'precio' => 3480.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Inodoro de 2 piezas',
                'marca' => 'Helvex',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['color' => 'Blanco', 'medida' => 'Descarga 4.8 L', 'precio' => 2890.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Lavabo cerámico',
                'marca' => 'Helvex',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['color' => 'Blanco', 'medida' => 'De empotrar', 'precio' => 1490.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Llave mezcladora para lavabo',
                'marca' => 'Urrea',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['color' => 'Cromado', 'precio' => 1265.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Regadera',
                'marca' => 'Helvex',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['color' => 'Cromado', 'medida' => 'Brazo 30 cm', 'precio' => 890.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Plomeria,
                'proveedor' => 'Hidráulica y Plomería Moderna',
                'familia' => 'Coladera de piso',
                'marca' => 'Urrea',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '10 x 10 cm', 'precio' => 148.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Electrico,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Cable THW calibre 12',
                'marca' => 'Condumex',
                'unidad' => UnidadMedida::Rollo,
                'variantes' => [
                    ['color' => 'Negro', 'presentacion' => 'Rollo 100 m', 'precio' => 1450.00],
                    ['color' => 'Blanco', 'presentacion' => 'Rollo 100 m', 'precio' => 1450.00],
                    ['color' => 'Rojo', 'presentacion' => 'Rollo 100 m', 'precio' => 1450.00],
                    ['color' => 'Verde', 'presentacion' => 'Rollo 100 m', 'precio' => 1450.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Electrico,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Cable THW calibre 14',
                'marca' => 'Condumex',
                'unidad' => UnidadMedida::Rollo,
                'variantes' => [
                    ['color' => 'Negro', 'presentacion' => 'Rollo 100 m', 'precio' => 1150.00],
                    ['color' => 'Blanco', 'presentacion' => 'Rollo 100 m', 'precio' => 1150.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Electrico,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Cable THW calibre 10',
                'marca' => 'Condumex',
                'unidad' => UnidadMedida::Rollo,
                'variantes' => [
                    ['color' => 'Negro', 'presentacion' => 'Rollo 100 m', 'precio' => 2150.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Electrico,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Cable THW calibre 8',
                'marca' => 'Condumex',
                'unidad' => UnidadMedida::Rollo,
                'variantes' => [
                    ['color' => 'Negro', 'presentacion' => 'Rollo 100 m', 'precio' => 3450.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Electrico,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Tubo conduit PVC',
                'marca' => 'Anamet',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '1/2"', 'presentacion' => 'Tramo 3 m', 'precio' => 128.00],
                    ['medida' => '3/4"', 'presentacion' => 'Tramo 3 m', 'precio' => 168.00],
                    ['medida' => '1"', 'presentacion' => 'Tramo 3 m', 'precio' => 235.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Electrico,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Caja eléctrica cuadrada',
                'marca' => 'Anamet',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '1/2" galvanizada', 'precio' => 45.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Electrico,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Apagador',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => 'Sencillo', 'color' => 'Blanco', 'precio' => 128.00],
                    ['medida' => 'De escalera', 'color' => 'Blanco', 'precio' => 185.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Electrico,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Contacto dúplex con tierra',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '15 A - 125 V', 'color' => 'Blanco', 'precio' => 178.00],
                    ['medida' => '15 A - 125 V', 'color' => 'Negro', 'precio' => 178.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Electrico,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Interruptor termomagnético',
                'marca' => 'Square D',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '1 x 20 A', 'precio' => 320.00],
                    ['medida' => '2 x 30 A', 'precio' => 865.00],
                    ['medida' => '2 x 40 A', 'precio' => 985.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Electrico,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Centro de carga',
                'marca' => 'Square D',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '12 espacios', 'precio' => 3520.00],
                    ['medida' => '24 espacios', 'precio' => 5860.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Electrico,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Panel LED',
                'marca' => 'Tecno Lite',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '30 x 30 cm 24 W', 'color' => 'Luz blanca', 'precio' => 288.00],
                    ['medida' => '60 x 60 cm 40 W', 'color' => 'Luz blanca', 'precio' => 520.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Electrico,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Reflejante LED',
                'marca' => 'Tecno Lite',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '100 W', 'precio' => 985.00],
                    ['medida' => '50 W', 'precio' => 620.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Electrico,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Cinta de aislar',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Rollo,
                'variantes' => [
                    ['color' => 'Negro', 'presentacion' => 'Rollo 18 m', 'precio' => 42.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Acabados,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Pintura vinílica',
                'marca' => 'Comex (Vinimex)',
                'unidad' => UnidadMedida::Cubeta,
                'variantes' => [
                    ['color' => 'Blanco', 'presentacion' => 'Cubeta 19 L', 'precio' => 1890.00],
                    ['color' => 'Hueso', 'presentacion' => 'Cubeta 19 L', 'precio' => 1890.00],
                    ['color' => 'Arena', 'presentacion' => 'Cubeta 19 L', 'precio' => 1890.00],
                    ['color' => 'Azul cielo', 'presentacion' => 'Cubeta 19 L', 'precio' => 1890.00],
                    ['color' => 'Verde manzana', 'presentacion' => 'Cubeta 19 L', 'precio' => 1890.00],
                    ['color' => 'Blanco', 'presentacion' => 'Cubeta 4 L', 'precio' => 465.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Acabados,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Pintura para exterior 5 años',
                'marca' => 'Comex',
                'unidad' => UnidadMedida::Cubeta,
                'variantes' => [
                    ['color' => 'Blanco', 'presentacion' => 'Cubeta 19 L', 'precio' => 2450.00],
                    ['color' => 'Beige', 'presentacion' => 'Cubeta 19 L', 'precio' => 2450.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Acabados,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Esmalte alquidálico',
                'marca' => 'Comex',
                'unidad' => UnidadMedida::Cubeta,
                'variantes' => [
                    ['color' => 'Blanco', 'presentacion' => 'Bote 4 L', 'precio' => 485.00],
                    ['color' => 'Negro', 'presentacion' => 'Bote 4 L', 'precio' => 485.00],
                    ['color' => 'Blanco', 'presentacion' => 'Cubeta 19 L', 'precio' => 1950.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Acabados,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Adhesivo para loseta',
                'marca' => 'Crest',
                'unidad' => UnidadMedida::Saco,
                'variantes' => [
                    ['medida' => 'Gris', 'presentacion' => 'Saco 20 kg', 'precio' => 178.00],
                    ['medida' => 'Blanco', 'presentacion' => 'Saco 20 kg', 'precio' => 195.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Acabados,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Boquilla para loseta',
                'marca' => 'Crest',
                'unidad' => UnidadMedida::Saco,
                'variantes' => [
                    ['medida' => 'Blanca', 'presentacion' => 'Saco 5 kg', 'precio' => 98.00],
                    ['medida' => 'Gris', 'presentacion' => 'Saco 5 kg', 'precio' => 98.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Acabados,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Loseta cerámica',
                'marca' => 'Interceramic',
                'unidad' => UnidadMedida::Caja,
                'variantes' => [
                    ['medida' => '45 x 45 cm', 'color' => 'Beige', 'presentacion' => 'Caja 1.62 m²', 'precio' => 165.00],
                    ['medida' => '45 x 45 cm', 'color' => 'Gris', 'presentacion' => 'Caja 1.62 m²', 'precio' => 165.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Acabados,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Porcelanato',
                'marca' => 'Interceramic',
                'unidad' => UnidadMedida::Caja,
                'variantes' => [
                    ['medida' => '60 x 60 cm', 'color' => 'Blanco pulido', 'presentacion' => 'Caja 1.44 m²', 'precio' => 395.00],
                    ['medida' => '60 x 60 cm', 'color' => 'Gris mate antiderrapante', 'presentacion' => 'Caja 1.44 m²', 'precio' => 425.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Acabados,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Zoclo cerámico',
                'marca' => 'Interceramic',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '8 x 60 cm', 'color' => 'Beige', 'precio' => 45.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Acabados,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Puerta de tambor',
                'marca' => 'Puertas MX',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '0.80 x 2.10 m', 'color' => 'Natural', 'precio' => 1380.00],
                    ['medida' => '0.90 x 2.10 m', 'color' => 'Natural', 'precio' => 1450.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Acabados,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Ventana de aluminio corrediza',
                'marca' => 'Cuprum',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '1.20 x 1.20 m', 'color' => 'Natural', 'precio' => 2890.00],
                    ['medida' => '1.50 x 1.20 m', 'color' => 'Natural', 'precio' => 3450.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Acabados,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Cerradura de pomo',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => 'Recámara', 'color' => 'Cromado', 'precio' => 245.00],
                    ['medida' => 'Baño', 'color' => 'Cromado', 'precio' => 215.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Acabados,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Bisagra',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '3 1/2" (juego 3 pzas)', 'color' => 'Negro', 'precio' => 85.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Carretilla',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '5 pies cúbicos', 'precio' => 1450.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Pala cuadrada',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => 'Con mango de madera', 'precio' => 285.00],
                    ['medida' => 'Con mango de fibra', 'precio' => 385.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Zapapico',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => 'Con mango', 'precio' => 385.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Cuchara de albañil',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '10"', 'precio' => 165.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Llana metálica',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '11 x 4"', 'precio' => 195.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Flexómetro',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '5 m', 'precio' => 145.00],
                    ['medida' => '8 m', 'precio' => 235.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Nivel de burbuja',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '60 cm', 'precio' => 285.00],
                    ['medida' => '90 cm', 'precio' => 395.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Martillo de uña',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '16 oz', 'precio' => 245.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Cincel',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '12"', 'precio' => 175.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Juego de llaves mixtas',
                'marca' => 'Urrea',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '12 piezas', 'precio' => 890.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Rotomartillo',
                'marca' => 'Bosch',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '650 W', 'precio' => 3450.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Revolvedora de concreto',
                'marca' => 'Cipsa',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '1 saco, 8 HP', 'precio' => 24500.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Materiales El Águila',
                'familia' => 'Escalera de tijera',
                'marca' => 'Cuprum',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '6 peldaños (1.83 m)', 'precio' => 2850.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Brocha',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '4"', 'precio' => 85.00],
                    ['medida' => '2"', 'precio' => 52.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Herramienta,
                'proveedor' => 'Pinturas y Acabados del Centro',
                'familia' => 'Rodillo de felpa',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '9"', 'precio' => 145.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Seguridad,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Casco de seguridad con ratchet',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['color' => 'Blanco', 'precio' => 285.00],
                    ['color' => 'Amarillo', 'precio' => 285.00],
                    ['color' => 'Naranja', 'precio' => 299.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Seguridad,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Arnés de seguridad',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '5 puntos con línea de vida', 'precio' => 1450.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Seguridad,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Guantes de carnaza',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => 'Par, talla grande', 'precio' => 145.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Seguridad,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Botas de seguridad con casquillo',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => 'Talla 27', 'color' => 'Café', 'precio' => 1250.00],
                    ['medida' => 'Talla 28', 'color' => 'Café', 'precio' => 1250.00],
                    ['medida' => 'Talla 29', 'color' => 'Café', 'precio' => 1290.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Seguridad,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Lentes de seguridad',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['color' => 'Claros', 'precio' => 120.00],
                    ['color' => 'Oscuros', 'precio' => 135.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Seguridad,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Chaleco reflejante',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['color' => 'Naranja', 'medida' => 'Malla, talla M', 'precio' => 185.00],
                    ['color' => 'Verde limón', 'medida' => 'Malla, talla M', 'precio' => 185.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Seguridad,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Tapones auditivos',
                'marca' => '3M',
                'unidad' => UnidadMedida::Caja,
                'variantes' => [
                    ['presentacion' => 'Caja 100 pares', 'precio' => 480.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Seguridad,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Mascarilla N95',
                'marca' => '3M',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['presentacion' => 'Pieza', 'precio' => 65.00],
                    ['presentacion' => 'Caja 20 piezas', 'precio' => 1150.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Seguridad,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Faja lumbar',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => 'Ajustable', 'precio' => 420.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Seguridad,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Cono de señalización',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Pieza,
                'variantes' => [
                    ['medida' => '70 cm', 'color' => 'Naranja', 'precio' => 380.00],
                ],
            ],
            [
                'categoria' => CategoriaMaterial::Seguridad,
                'proveedor' => 'Eléctricos y Seguridad Industrial',
                'familia' => 'Cinta de precaución',
                'marca' => 'Truper',
                'unidad' => UnidadMedida::Rollo,
                'variantes' => [
                    ['medida' => 'Peligro 300 m', 'color' => 'Amarillo', 'precio' => 285.00],
                ],
            ],
        ];
    }
}
