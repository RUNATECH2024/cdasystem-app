<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function store(StoreProductoRequest $request)
    {
        $datos = $request->validated();
        $atributos = $datos['atributos'] ?? [];
        unset($datos['atributos']);

        $producto = DB::transaction(function () use ($datos, $atributos) {
            $producto = Producto::create($datos);

            foreach ($atributos as $atributo) {
                $producto->atributos()->create($atributo);
            }

            return $producto->load('subcategoria', 'atributos');
        });

        return response()->json([
            'mensaje' => 'Producto creado correctamente',
            'producto' => new ProductoResource($producto),
        ], 201);
    }
}
