<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre'     => ['required', 'string', 'max:255'],
            'email'      => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(Usuario::class)->ignore($this->user()->id),
            ],
            'telefono'   => ['nullable', 'string', 'max:20'],
            'foto_perfil'=> ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'      => 'El nombre es obligatorio.',
            'email.required'       => 'El correo electrónico es obligatorio.',
            'email.unique'         => 'Este correo ya está registrado por otro usuario.',
            'foto_perfil.image'    => 'El archivo debe ser una imagen.',
            'foto_perfil.max'      => 'La imagen no debe superar 2 MB.',
        ];
    }
}
