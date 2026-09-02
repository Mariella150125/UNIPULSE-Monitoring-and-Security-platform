<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // à remplacer par ta policy si nécessaire
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:100'],
            'scopes'     => ['required', 'array', 'min:1'],
            'scopes.*'   => ['string', 'regex:/^(servers|applications|alerts|reports):(read|write)$/'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Le nom de la clé est obligatoire.',
            'scopes.required'    => 'Sélectionne au moins une permission.',
            'scopes.*.regex'     => 'Format de permission invalide (ex: servers:read).',
            'expires_at.after'   => 'La date d\'expiration doit être dans le futur.',
        ];
    }
}