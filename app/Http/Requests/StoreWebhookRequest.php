<?php

namespace App\Http\Requests;



use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'direction'          => ['required', 'in:inbound,outbound'],
            'scope'              => ['required', 'in:application,platform'],
            'name'               => ['required', 'string', 'max:150'],
            'connector_id'       => ['nullable', 'exists:connectors,id'],
            'application_id'     => ['nullable', 'exists:applications,id'],
            //si cést outbound
            'target_url'         => ['required_if:direction,outbound', 'nullable', 'url'],
            'auth_method'        => ['required', 'in:hmac_signature,api_key,none'],
            'api_key_id'         => ['nullable', 'exists:api_keys,id'],
            'min_severity_level' => ['nullable', 'integer', 'min:0', 'max:4'],
            'event_types'        => ['required', 'array', 'min:1', 'max:20'],
            'event_types.*'      => ['integer', 'exists:webhook_event_types,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $scope      = $this->input('scope');
            $direction  = $this->input('direction');
            $authMethod = $this->input('auth_method');

            // scope = application → application_id obligatoire
            if ($scope === 'application' && !$this->input('application_id')) {
                $validator->errors()->add(
                    'application_id',
                    'Une application est obligatoire pour le scope "application".'
                );
            }

            // scope = platform → application_id interdit
            if ($scope === 'platform' && $this->input('application_id')) {
                $validator->errors()->add(
                    'application_id',
                    'Aucune application à lier pour le scope "platform".'
                );
            }

            // auth_method = api_key → api_key_id obligatoire
            if ($authMethod === 'api_key' && !$this->input('api_key_id')) {
                $validator->errors()->add(
                    'api_key_id',
                    'Une clé API est requise pour cette méthode d\'authentification.'
                );
            }

            // Vérifie que les event_types sont compatibles avec la direction
            if (!$validator->errors()->any() && $this->input('event_types')) {
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

    public function messages(): array
    {
        return [
            'name.required'              => 'Le nom du webhook est obligatoire.',
            'target_url.required'        => 'L\'URL cible est obligatoire.',
            'target_url.url'             => 'L\'URL cible n\'est pas valide.',
            'event_types.required'       => 'Sélectionne au moins un événement.',
            'event_types.min'            => 'Sélectionne au moins un événement.',
            'event_types.*.exists'       => 'Un ou plusieurs événements sélectionnés n\'existent pas.',
        ];
    }
}