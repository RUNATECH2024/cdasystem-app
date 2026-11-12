<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_producto' => $this->id_producto,
            'id_subcategoria' => $this->id_subcategoria,
            'codigo_barras' => $this->codigo_barras,
            'sku' => $this->sku,
            'nombre' => $this->nombre,
            'marca' => $this->marca,
            'descripcion_corta' => $this->descripcion_corta,
            'descripcion_larga' => $this->descripcion_larga,
            'precio_venta' => (float) $this->precio_venta,
            'stock_actual' => $this->stock_actual,
            'stock_minimo' => $this->stock_minimo,
            'imagen_principal' => $this->imagen_principal,
            'destacado' => $this->destacado,
            'es_repuesto' => $this->es_repuesto,
            'activo' => $this->activo,
            'atributos' => AtributoProductoResource::collection($this->whenLoaded('atributos')),
        ];
    }
}
