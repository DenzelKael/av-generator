<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => [
                'required',
                'file',
                'max:20480',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'Debe seleccionar un archivo.',
            'document.file' => 'El documento debe ser un archivo válido.',
            'document.max' => 'El archivo no puede superar los 20 MB.',
        ];
    }
}
