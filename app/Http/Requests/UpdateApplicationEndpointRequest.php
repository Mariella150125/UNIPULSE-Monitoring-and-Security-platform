<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicationEndpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url'                => ['sometimes', 'required', 'url', 'max:500'],
            'http_method'        => ['sometimes', 'required', 'in:GET,POST,PUT,DELETE,HEAD,OPTIONS'],
            'auth_headers'       => ['nullable', 'array'],
            'auth_headers.*.key' => ['required_with:auth_headers', 'string', 'max:100'],
            'auth_headers.*.value' => ['required_with:auth_headers', 'string', 'max:500'],
            'frequency_seconds'  => ['sometimes', 'required', 'integer', 'min:10', 'max:86400'],
        ];
    }

    public function formattedHeaders(): ?array
    {
        $headers = $this->input('auth_headers');

        if (!$headers) {
            return null;
        }

        return collect($headers)->mapWithKeys(fn ($h) => [$h['key'] => $h['value']])->all();
    }
}
