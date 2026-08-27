<?php

namespace App\Services\Connectors;

use App\Models\Connector;
use Illuminate\Support\Facades\Http;

abstract class BaseConnectorTester
{
    public function __construct(
        protected Connector $connector,
    ) {}

    /**
     * Lance le test de connexion.
     */
    abstract public function test(): ConnectorTestResult;

    /**
     * Construit un client HTTP pré-configuré avec les infos du connecteur.
     */
    protected function makeClient(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout(10);

        if ($this->connector->auth_username) {
            $client = $client->withBasicAuth(
                $this->connector->auth_username,
                $this->connector->decryptPassword(),
            );
        }

        return $client;
    }

    /**
     * Retourne l'URL d'endpoint complète.
     */
    protected function buildUrl(string $path = ''): string
    {
        return rtrim($this->connector->full_url, '/') . '/' . ltrim($path, '/');
    }
}