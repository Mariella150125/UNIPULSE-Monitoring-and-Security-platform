<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['sometimes', 'required', 'string', 'max:150'],
            'target_url'         => ['sometimes', 'required', 'url', 'max:500'],
            'auth_method'        => ['sometimes', 'required', 'in:hmac_signature,api_key,none'],
            'api_key_id'         => ['nullable', 'exists:api_keys,id'],
            'min_severity_level' => ['nullable', 'integer', 'min:0', 'max:4'],
            'event_types'        => ['sometimes', 'nullable', 'array', 'min:1', 'max:20'],
            'event_types.*'      => ['integer', 'exists:webhook_event_types,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $authMethod = $this->input('auth_method');

            if ($authMethod === 'api_key' && !$this->input('api_key_id')) {
                $validator->errors()->add(
                    'api_key_id',
                    'Une clé API est requise pour cette méthode d\'authentification.'
                );
            }

            // Si les event_types sont mis à jour, vérifier la compatibilité direction
            if (
                !$validator->errors()->any()
                && $this->input('event_types')
                && $this->route('webhook')
            ) {
                $direction = $this->route('webhook')->direction;
                $incompatible = \App\Models\WebhookEventType::whereIn('id', $this->input('event_types'))
                    ->whereNotIn('applicable_direction', [$direction, 'both'])
                    ->pluck('code')
                    ->all();

                if ($incompatible) {
                    $validator->errors()->add(
                        'event_types',
                        'Événements incompatibles avec la direction ' . $direction . ' : ' . implode(', ', $incompatible)
                    );
                }
            }
        });
    }
}