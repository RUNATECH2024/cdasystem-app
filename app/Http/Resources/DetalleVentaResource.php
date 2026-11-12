<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetalleVentaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_detalle' => $this->id_detalle,
            'id_producto' => $this->id_producto,
            'cantidad' => $this->cantidad,
            'precio_unitario' => (float) $this->precio_unitario,
            'producto' => $this->whenLoaded('producto', fn () => new ProductoResource($this->producto)),
        ];
    }
}
