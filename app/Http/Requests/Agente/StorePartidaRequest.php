<?php

namespace App\Http\Requests\Agente;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePartidaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'material_id' => ['required', 'integer', 'exists:materiales,id'],
            'cantidad' => ['required', 'numeric', 'min:0.01', 'max:99999'],
        ];
    }
}
