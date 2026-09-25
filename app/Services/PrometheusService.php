<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Connector;

class PrometheusService
{
    protected ?string $baseUrl;

    public function __construct()
    {
        $connector = Connector::where('type', 'prometheus')->first();
        if ($connector) {
            // --- CORRECTION : On construit l'URL AVEC LE PORT ---
            $base = rtrim($connector->base_url, '/');
            $port = $connector->api_port ? ':' . $connector->api_port : '';
            $this->baseUrl = $base . $port;
        } else {
            $this->baseUrl = null;
        }
    }

    public function isConfigured(): bool
    {
        $active = \App\Models\Setting::get('prom_active', '1') == '1';
        return $active && !empty($this->baseUrl);
    }

    public function query(string $query): ?array
    {
        if (!$this->isConfigured()) return null;

        $timeout = (int) \App\Models\Setting::get('prom_timeout', 10);

        try {
            // On ajoute withoutVerifying() pour éviter les bugs SSL
            $response = Http::timeout($timeout)->withoutVerifying()->get($this->baseUrl . '/api/v1/query', [
                'query' => $query,
            ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function queryRange(string $query, int $hours = 24, string $step = '1h'): array
    {
        if (!$this->isConfigured()) return [];

        $end = time();
        $start = $end - ($hours * 3600);

        try {
            $response = Http::timeout(30)->withoutVerifying()->get($this->baseUrl . '/api/v1/query_range', [
                'query' => $query,
                'start' => $start,
                'end'   => $end,
                'step'  => $step
            ]);

            if (!$response->successful()) return [];

            $result = $response->json('data.result.0.values');
            if (empty($result)) return [];

            return array_map(fn($item) => [
                'x' => date('H:i', $item[0]),
                'y' => (float) $item[1]
            ], $result);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getServerStatus(string $instance): ?float
    {
        $result = $this->query('up{instance="' . $instance . '"}');
        $value = data_get($result, 'data.result.0.value.1');
        return $value !== null ? (float) $value : null;
    }

    public function getCpuUsage(string $instance): ?float
    {
        $query = '100 - (avg by (instance) (rate(node_cpu_seconds_total{instance="' . $instance . '",mode="idle"}[5m])) * 100)';
        $result = $this->query($query);
        $value = data_get($result, 'data.result.0.value.1');
        return $value !== null ? round((float) $value, 2) : null;
    }

    public function getMemoryUsage(string $instance): ?float
    {
        $query = '(1 - node_memory_MemAvailable_bytes{instance="' . $instance . '"} / node_memory_MemTotal_bytes{instance="' . $instance . '"}) * 100';
        $result = $this->query($query);
        $value = data_get($result, 'data.result.0.value.1');
        return $value !== null ? round((float) $value, 2) : null;
    }

    public function getDiskUsage(string $instance): ?float
    {
        $query = '(1 - (node_filesystem_avail_bytes{instance="' . $instance . '", mountpoint="/"} / node_filesystem_size_bytes{instance="' . $instance . '", mountpoint="/"})) * 100';
        $result = $this->query($query);
        $value = data_get($result, 'data.result.0.value.1');
        return $value !== null ? round((float) $value, 2) : null;
    }

    public function getUptime(string $instance): ?int
    {
        $query = 'time() - node_boot_time_seconds{instance="' . $instance . '"}';
        $result = $this->query($query);
        $value = data_get($result, 'data.result.0.value.1');
        return $value !== null ? (int) $value : null;
    }

    public function getNetworkTraffic(string $instance): ?float
    {
        $query = '(sum(rate(node_network_receive_bytes_total{instance="' . $instance . '", device!="lo"}[5m])) + sum(rate(node_network_transmit_bytes_total{instance="' . $instance . '", device!="lo"}[5m]))) / 1048576';
        $result = $this->query($query);
        $value = data_get($result, 'data.result.0.value.1');
        return $value !== null ? round((float) $value, 2) : null;
    }

    public function getDnsLookup(string $instance): ?float
    {
        $query = 'probe_dns_lookup_time_seconds{instance="' . $instance . '"}';
        $result = $this->query($query);
        $value = data_get($result, 'data.result.0.value.1');
        return $value !== null ? round((float) $value * 1000, 2) : null;
    }
}