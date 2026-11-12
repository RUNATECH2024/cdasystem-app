<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRepuestoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_producto' => 'required|integer|exists:productos,id_producto',
            'cantidad' => 'required|integer|min:1|max:999',
        ];
    }
}
