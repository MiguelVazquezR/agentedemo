<?php

namespace App\Http\Requests\Agente;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBorradorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'obra_id' => ['nullable', 'integer', 'exists:obras,id'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'fecha_requerida' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
