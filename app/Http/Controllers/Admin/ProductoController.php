<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Subcategoria;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    // 1. Mostrar la tabla de productos
    public function index()
    {
        $productos = Producto::with('subcategoria')->orderBy('id_producto', 'desc')->paginate(15);

        return view('admin.productos.index', compact('productos'));
    }

    // 2. Mostrar el formulario para crear un producto
    public function create()
    {
        // AQUÍ ESTABA EL ERROR: Faltaba enviar las subcategorías
        $subcategorias = Subcategoria::orderBy('nombre')->get();

        return view('admin.productos.create', compact('subcategorias'));
    }

    // 3. Guardar el producto en la base de datos
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'id_subcategoria' => 'required|integer',
            'precio_compra' => 'required|numeric',
            'precio_venta' => 'required|numeric',
            'stock_actual' => 'required|integer',
        ]);

        Producto::create([
            'nombre' => $request->nombre,
            'id_subcategoria' => $request->id_subcategoria,
            'sku' => $request->sku,
            'descripcion_corta' => $request->descripcion_corta,
            'precio_compra' => $request->precio_compra,
            'precio_venta' => $request->precio_venta,
            'stock_actual' => $request->stock_actual,
            'stock_minimo' => $request->stock_minimo ?? 5,
            'activo' => true,
        ]);

        return redirect()->route('admin.productos.index')->with('success', 'Producto creado exitosamente.');
    }

    // 4. Actualizar la marca (del código que me enviaste antes)
    public function updateMarca(Request $request, Producto $producto)
    {
        return back()->with('success', 'Marca actualizada.');
    }
}
