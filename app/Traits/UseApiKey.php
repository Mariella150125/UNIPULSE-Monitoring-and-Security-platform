<?php
namespace App\Traits;

use App\Models\ApiKey;

trait UsesApiKey
{
    protected function currentApiKey(): ?ApiKey
    {
        return request()->attributes->get('api_key');
    }

    protected function currentApiKeyOrFail(): ApiKey
    {
        return $this->currentApiKey() ?? abort(401, 'Clé API requise.');
    }
}