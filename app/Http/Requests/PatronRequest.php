<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatronRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     */
    public function authorize(): bool
    {
        // Lo dejamos en true para permitir que cualquier usuario logueado lo use.
        // Puedes añadir lógica de permisos aquí si lo necesitas.
        return true;
    }

    /**
     * Obtiene las reglas de validación que aplican a la petición.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Obtenemos el ID del patrón desde la ruta, si existe (para la actualización)
        $patronId = $this->route('patron') ? $this->route('patron')->id_patron : null;

        return [
            'nombre_comercial' => 'required|string|max:255',
            // La razón social y el RFC deben ser únicos, excepto para el registro que se está editando
            'razon_social' => [
                'required',
                'string',
                'max:255',
                Rule::unique('patrones')->ignore($patronId, 'id_patron'),
            ],
            'rfc' => [
                'required',
                'string',
                'max:13',
                Rule::unique('patrones')->ignore($patronId, 'id_patron'),
            ],
            'tipo_persona' => 'required|in:fisica,moral',
            'direccion_fiscal' => 'nullable|string',
            'actividad_principal' => 'nullable|string',
            'representante_legal' => 'nullable|string|max:255',
            // El logo es opcional, debe ser una imagen y no pesar más de 2MB (2048 KB)
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
