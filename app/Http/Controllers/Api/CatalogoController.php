<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoriaResource;
use App\Http\Resources\ProductoResource;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    // Devuelve el menú para tu frontend (Computadoras, Componentes, etc.)
    public function menu()
    {
        $categorias = Categoria::with('subcategorias')
            ->where('activo', true)
            ->orderBy('orden_visual')
            ->get();

        return CategoriaResource::collection($categorias);
    }

    // Devuelve los productos de una subcategoría (Ej: Solo Laptops)
    public function productosPorSubcategoria($id_subcategoria)
    {
        $productos = Producto::with('atributos')
            ->where('id_subcategoria', (int) $id_subcategoria)
            ->where('activo', true)
            ->get();

        return ProductoResource::collection($productos);
    }

    // Buscador de productos
    public function buscar(Request $request)
    {
        $query = mb_substr(trim((string) $request->input('q')), 0, 100);

        if ($query === '') {
            return response()->json([]);
        }

        $like = '%'.mb_strtolower($query).'%';

        $productos = Producto::where('activo', true)
            ->where(function ($subQuery) use ($like) {
                $subQuery->whereRaw('LOWER(nombre) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(sku) LIKE ?', [$like]);
            })
            ->get();

        return ProductoResource::collection($productos);
    }
}
