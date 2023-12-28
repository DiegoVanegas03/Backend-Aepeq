<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string',
            'numero_celular' => 'required|string',
            'estado_provincia' => 'required|string',
            'pais' => 'required|string',
            'ocupacion' => 'required|string',
            'lugar_trabajo' => 'required|string',
            'tipo_inscripcion' => 'required|string',
            'escuela' => 'nullable|string',
            'asociacion' => 'nullable|string',
            'documento_certificado' => 'nullable|file',
            'beca_pago' => 'required|string',
            'metodo_pago' => 'nullable|string',
            'comprobante_pago' => 'nullable|file',
            'promotor' => 'nullable|string',
            'token_de_registro' => 'nullable|string',
        ];
    }
}
