<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrdenServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_cliente' => 'required|integer|exists:clientes,id_cliente',
            'id_equipo' => 'required|integer|exists:equipos_cliente,id_equipo',
            'id_tecnico' => [
                'nullable', 'integer',
                Rule::exists('usuarios', 'id_usuario')->where(fn ($query) => $query
                    ->where('rol', 'Tecnico')
                    ->where('activo', true)),
            ],
            'falla_reportada' => 'required|string|max:5000',
            'diagnostico' => 'nullable|string|max:5000',
            'contador_ingreso' => 'nullable|integer|min:0',
            'total_mano_obra' => 'nullable|numeric|min:0|max:99999999.99',
        ];
    }
}
