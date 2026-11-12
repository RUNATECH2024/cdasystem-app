<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_cliente' => 'nullable|integer|exists:clientes,id_cliente',
            'metodo_pago' => ['nullable', Rule::in(['Efectivo', 'Tarjeta', 'Transferencia', 'Credito'])],
            'estado' => ['nullable', Rule::in(['Pagada', 'Pendiente'])],
            'impuesto' => 'nullable|numeric|min:0|max:99999999.99',
            'items' => 'required|array|min:1|max:100',
            'items.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'items.*.cantidad' => 'required|integer|min:1|max:999',
        ];
    }
}
