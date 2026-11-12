<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubcategoriaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_subcategoria' => $this->id_subcategoria,
            'id_categoria' => $this->id_categoria,
            'nombre' => $this->nombre,
            'imagen_portada' => $this->imagen_portada,
            'orden_visual' => $this->orden_visual,
            'activo' => $this->activo,
            'productos' => ProductoResource::collection($this->whenLoaded('productos')),
        ];
    }
}
