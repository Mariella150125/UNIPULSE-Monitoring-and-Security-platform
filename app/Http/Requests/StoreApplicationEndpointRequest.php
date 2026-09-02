<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationEndpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_id'     => ['required', 'exists:applications,id'],
            'url'                => ['required', 'url', 'max:500'],
            'http_method'        => ['required', 'in:GET,POST,PUT,DELETE,HEAD,OPTIONS'],
            'auth_headers'       => ['nullable', 'array'],
            'auth_headers.*.key' => ['required_with:auth_headers', 'string', 'max:100'],
            'auth_headers.*.value' => ['required_with:auth_headers', 'string', 'max:500'],
            'frequency_seconds'  => ['required', 'integer', 'min:10', 'max:86400'],
        ];
    }

    public function messages(): array
    {
        return [
            'application_id.required'    => 'L\'application est obligatoire.',
            'url.required'               => 'L\'URL est obligatoire.',
            'url.url'                    => 'L\'URL n\'est pas valide.',
            'http_method.required'       => 'La méthode HTTP est obligatoire.',
            'http_method.in'             => 'Méthode HTTP non autorisée.',
            'frequency_seconds.min'      => 'La fréquence minimale est de 10 secondes.',
            'frequency_seconds.max'      => 'La fréquence maximale est de 24 heures.',
        ];
    }

    /**
     * Transforme le tableau plat [{key, value}] en tableau associatif
     * pour le stockage JSON.
     */
    public function formattedHeaders(): ?array
    {
        $headers = $this->input('auth_headers');

        if (!$headers) {
            return null;
        }

        return collect($headers)->mapWithKeys(fn ($h) => [$h['key'] => $h['value']])->all();
    }
}