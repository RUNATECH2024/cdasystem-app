<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_categoria' => $this->id_categoria,
            'nombre' => $this->nombre,
            'icono' => $this->icono,
            'orden_visual' => $this->orden_visual,
            'activo' => $this->activo,
            'subcategorias' => SubcategoriaResource::collection($this->whenLoaded('subcategorias')),
        ];
    }
}
