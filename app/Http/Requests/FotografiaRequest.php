<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FotografiaRequest extends FormRequest
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
            'nombre_fotografia'=>'required|string',
            'lugar_y_fecha' =>'required|string',
            'descripcion'=>'required|string',
            'fotografia'=>'required|file',
        ];
    }
}
