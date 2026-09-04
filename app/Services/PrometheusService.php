<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Connector;

class PrometheusService
{
    protected ?string $baseUrl;

    public function __construct()
    {
        // On va chercher l'URL de Prometheus dans ta table "connectors"
        $connector = Connector::where('type', 'prometheus')->first();
        $this->baseUrl = $connector?->base_url;
    }

    // Vérifie si Prometheus est configuré
    public function isConfigured(): bool
    {
        return !empty($this->baseUrl);
    }

    // Exécute une requête PromQL
    public function query(string $query): ?array
    {
        if (!$this->isConfigured()) return null;

        $response = Http::timeout(10)->get($this->baseUrl . '/api/v1/query', [
            'query' => $query,
        ]);

        return $response->successful() ? $response->json() : null;
    }

    // 1. Statut (Online/Offline)
    public function getServerStatus(string $instance): ?float
    {
        $result = $this->query('up{instance="' . $instance . '"}');
        return data_get($result, 'data.result.0.value.1');
    }

    // 2. CPU (en %)
    public function getCpuUsage(string $instance): ?float
    {
        $query = '100 - (avg by (instance) (rate(node_cpu_seconds_total{instance="' . $instance . '",mode="idle"}[5m])) * 100)';
        $result = $this->query($query);
        $value = data_get($result, 'data.result.0.value.1');
        return $value !== null ? round((float) $value, 2) : null;
    }

    // 3. RAM (en %)
    public function getMemoryUsage(string $instance): ?float
    {
        $query = '(1 - node_memory_MemAvailable_bytes{instance="' . $instance . '"} / node_memory_MemTotal_bytes{instance="' . $instance . '"}) * 100';
        $result = $this->query($query);
        $value = data_get($result, 'data.result.0.value.1');
        return $value !== null ? round((float) $value, 2) : null;
    }
}