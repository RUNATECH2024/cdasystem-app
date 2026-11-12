<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_subcategoria' => 'nullable|integer|exists:subcategorias,id_subcategoria',
            'codigo_barras' => 'nullable|string|max:50|unique:productos,codigo_barras',
            'sku' => 'nullable|string|max:50|unique:productos,sku',
            'nombre' => 'required|string|max:150',
            'marca' => 'nullable|string|max:80',
            'descripcion_corta' => 'nullable|string|max:255',
            'descripcion_larga' => 'nullable|string|max:5000',
            'precio_compra' => 'nullable|numeric|min:0|max:99999999.99',
            'precio_venta' => 'required|numeric|min:0|max:99999999.99',
            'stock_actual' => 'nullable|integer|min:0|max:1000000',
            'stock_minimo' => 'nullable|integer|min:0|max:1000000',
            'fecha_vencimiento' => 'nullable|date',
            'imagen_principal' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/'],
            'destacado' => 'nullable|boolean',
            'es_repuesto' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
            'atributos' => 'nullable|array|max:50',
            'atributos.*.nombre_atributo' => 'required_with:atributos|string|max:50',
            'atributos.*.valor_atributo' => 'required_with:atributos|string|max:100',
        ];
    }
}
