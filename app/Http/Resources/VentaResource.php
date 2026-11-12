<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VentaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_venta' => $this->id_venta,
            'id_cliente' => $this->id_cliente,
            'id_vendedor' => $this->id_vendedor,
            'fecha' => $this->fecha,
            'fecha_vencimiento' => $this->fecha_vencimiento,
            'subtotal' => (float) $this->subtotal,
            'impuesto' => (float) $this->impuesto,
            'total' => (float) $this->total,
            'metodo_pago' => $this->metodo_pago,
            'estado' => $this->estado,
            'detalles' => DetalleVentaResource::collection($this->whenLoaded('detalles')),
        ];
    }
}
