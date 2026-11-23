<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:200'],
            'message' => ['nullable', 'string', 'max:2000'],
            // La pertenencia a la clínica la valida CreateLead (servicio de otra clínica = se ignora).
            'service_id' => ['nullable', 'integer'],
            'source' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escribe tu nombre.',
            'name.max' => 'El nombre es demasiado largo.',
            'phone.required' => 'Escribe tu número de WhatsApp o teléfono.',
            'phone.max' => 'El teléfono es demasiado largo.',
            'email.email' => 'Escribe un correo válido.',
            'service_id.integer' => 'Selecciona un tratamiento de la lista.',
        ];
    }
}
