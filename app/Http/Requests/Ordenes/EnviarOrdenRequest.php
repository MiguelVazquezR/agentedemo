<?php

namespace App\Http\Requests\Ordenes;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EnviarOrdenRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comentario' => ['nullable', 'string', 'max:1000'],
            'cc' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Mensaje que se agrega al cuerpo del correo.
     */
    public function comentario(): ?string
    {
        $comentario = trim((string) $this->string('comentario'));

        return $comentario === '' ? null : $comentario;
    }

    /**
     * Correos válidos escritos en copia, separados por comas.
     *
     * @return array<int, string>
     */
    public function copias(): array
    {
        return collect(explode(',', (string) $this->string('cc')))
            ->map(fn (string $correo): string => trim($correo))
            ->filter(fn (string $correo): bool => filter_var($correo, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }
}
