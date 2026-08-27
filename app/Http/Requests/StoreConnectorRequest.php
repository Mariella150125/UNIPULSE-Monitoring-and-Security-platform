<?php

namespace App\Http\Requests;

use App\Rules\SafeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ConnectorType;

class StoreConnectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'          => ['required', new Enum(\App\Enums\ConnectorType::class)],
            'name'          => ['required', 'string', 'max:255'],
            'base_url'      => ['required', 'url', 'max:500', new SafeUrl],
            'auth_username' => ['nullable', 'string', 'max:255'],
            'auth_password' => ['nullable', 'string', 'max:500'],
            'api_port'      => ['nullable', 'integer', 'min:1', 'max:65535'],
            'extra_config'  => ['nullable', 'array', 'max:50'],
            'extra_config.*' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function passedValidation(): void
    {
        // Parser extra_config_raw → extra_config
        if ($this->has('extra_config_raw')) {
            $raw = $this->input('extra_config_raw');
            $this->merge([
                'extra_config' => (is_string($raw) && trim($raw) !== '') ? json_decode($raw, true) : null,
            ]);
        }

        // Mapper auth_password → auth_password_encrypted
        if ($this->has('auth_password')) {
            $this->merge([
                'auth_password_encrypted' => $this->input('auth_password'),
            ]);
        }
    }
}