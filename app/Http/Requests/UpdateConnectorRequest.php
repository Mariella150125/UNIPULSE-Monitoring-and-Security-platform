<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ConnectorType;

class UpdateConnectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $connectorId = $this->route('connector');

        return [
            'type'                   => ['sometimes', 'required', new Enum(ConnectorType::class)],
            'name'                   => ['sometimes', 'required', 'string', 'max:255'],
            'base_url'               => ['sometimes', 'required', 'url', 'max:500'],
            'auth_username'          => ['nullable', 'string', 'max:255'],
            'auth_password'          => ['nullable', 'string', 'max:500'],
            'api_port'               => ['nullable', 'integer', 'min:1', 'max:65535'],
            'extra_config'           => ['nullable', 'array'],
            'extra_config.*'         => ['nullable'],
        ];
    }
    

    protected function passedValidation(): void
    {
        if ($this->has('extra_config_raw')) {
            $raw = $this->input('extra_config_raw');

            $this->merge([
                'extra_config' => (
                    is_string($raw) && trim($raw) !== ''
                )
                    ? json_decode($raw, true)
                    : null,
            ]);
        }

        if ($this->has('auth_password')) {
            $this->merge([
                'auth_password_encrypted' => $this->input('auth_password'),
            ]);
        }
    }
}