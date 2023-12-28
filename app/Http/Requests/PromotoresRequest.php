<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromotoresRequest extends FormRequest
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
            'tokenUrl' => 'required|string',
            'mail'=> 'required|email',
            'nombre' => 'required|string',
            'numero_de_becas' => 'required|integer',
            'precio_total' => 'required|integer',
            'precio_por_beca' => 'required|integer',
            'beneficiados'=> 'required|string',
            'comprobante_de_pago' =>'required|file',
            'factura' => 'required|string',
            'cfdi' => 'nullable|file',
        ];
    }
}
