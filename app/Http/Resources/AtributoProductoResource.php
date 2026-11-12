<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AtributoProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_atributo' => $this->id_atributo,
            'nombre_atributo' => $this->nombre_atributo,
            'valor_atributo' => $this->valor_atributo,
        ];
    }
}
