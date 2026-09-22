<?php

namespace App\Http\Controllers\Agente;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    /**
     * Buscador del catálogo para agregar partidas a mano.
     */
    public function index(Request $request): JsonResponse
    {
        $termino = trim((string) $request->query('buscar', ''));

        $materiales = Material::query()
            ->where('activo', true)
            ->when($termino !== '', fn ($query) => $query->buscar($termino))
            ->orderBy('familia')
            ->orderBy('id')
            ->limit(24)
            ->get();

        return response()->json([
            'materiales' => $materiales
                ->map(fn (Material $material): array => [
                    'id' => $material->id,
                    ...$material->paraAgente(),
                    'descripcion' => $material->descripcionCompleta(),
                    'unidad_simbolo' => $material->unidad->simbolo(),
                ])
                ->all(),
        ]);
    }
}
