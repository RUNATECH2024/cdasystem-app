<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdenServicioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_orden' => $this->id_orden,
            'id_cliente' => $this->id_cliente,
            'id_equipo' => $this->id_equipo,
            'id_tecnico' => $this->id_tecnico,
            'fecha_ingreso' => $this->fecha_ingreso,
            'fecha_entrega' => $this->fecha_entrega,
            'falla_reportada' => $this->falla_reportada,
            'diagnostico' => $this->diagnostico,
            'contador_ingreso' => $this->contador_ingreso,
            'contador_salida' => $this->contador_salida,
            'estado' => $this->estado,
            'total_mano_obra' => (float) $this->total_mano_obra,
            'total_repuestos' => (float) $this->total_repuestos,
            'total_general' => (float) $this->total_general,
            'repuestos' => DetalleRepuestosOsResource::collection($this->whenLoaded('repuestos')),
        ];
    }
}
