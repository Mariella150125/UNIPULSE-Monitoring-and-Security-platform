<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationEndpointRequest;
use App\Http\Requests\UpdateApplicationEndpointRequest;
use App\Models\ApplicationEndpoint;
use App\Services\EndpointHealthChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationEndpointController extends Controller
{
    public function __construct(
        private EndpointHealthChecker $healthChecker
    ) {}

    /**
     * Liste les endpoints avec leurs KPI.
     * ?application_id=X pour filtrer par application.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ApplicationEndpoint::with('application');

        if ($request->filled('application_id')) {
            $query->where('application_id', $request->application_id);
        }

        $endpoints = $query->orderByDesc('updated_at')->get()->map(fn ($ep) => [
            'id'                    => $ep->id,
            'application_id'        => $ep->application_id,
            'application_name'      => $ep->application?->name,
            'url'                   => $ep->url,
            'url_short'             => $this->shortenUrl($ep->url),
            'http_method'           => $ep->http_method,
            'frequency_seconds'     => $ep->frequency_seconds,
            'frequency_label'       => $this->formatFrequency($ep->frequency_seconds),
            'last_status'           => $ep->last_status,
            'last_response_time_ms' => $ep->last_response_time_ms,
            'last_checked_at'       => $ep->last_checked_at?->diffForHumans(),
            'has_headers'           => !empty($ep->auth_headers),
        ]);

        // --- KPI pour les cartes en haut de page ---
        $now       = now();
        $last24h   = $now->copy()->subDay();

        $total = ApplicationEndpoint::count();

        $healthy = ApplicationEndpoint::where('last_status', 'success')
            ->orWhere('last_status', 'never_checked')
            ->count();

        $errors24h = ApplicationEndpoint::where('last_checked_at', '>=', $last24h)
            ->whereIn('last_status', ['timeout', 'http_4xx', 'http_5xx'])
            ->count();

        $avgResponseTime = ApplicationEndpoint::where('last_status', 'success')
            ->whereNotNull('last_response_time_ms')
            ->avg('last_response_time_ms');

        $stats = [
            'total'             => $total,
            'healthy'           => $healthy,
            'errors_24h'        => $errors24h,
            'avg_response_ms'   => $avgResponseTime ? round($avgResponseTime) : null,
        ];

        return response()->json([
            'endpoints' => $endpoints,
            'stats'     => $stats,
        ]);
    }

    /**
     * Crée un nouvel endpoint PULL.
     */
    public function store(StoreApplicationEndpointRequest $request): JsonResponse
    {
        $endpoint = ApplicationEndpoint::create([
            'application_id'     => $request->application_id,
            'url'                => $request->url,
            'http_method'        => $request->http_method,
            'auth_headers'       => $request->formattedHeaders(),
            'frequency_seconds'  => $request->frequency_seconds,
        ]);

        return response()->json([
            'message'  => 'Endpoint ajouté.',
            'endpoint' => $this->formatEndpoint($endpoint),
        ], 201);
    }

    /**
     * Détail d'un endpoint — utilisé par le panneau "Exemple de requête".
     */
    public function show(ApplicationEndpoint $applicationEndpoint): JsonResponse
    {
        $endpoint = $applicationEndpoint->load('application');

        $data = $this->formatEndpoint($endpoint);
        $data['auth_headers'] = $endpoint->auth_headers; // décrypté automatiquement par le cast
        $data['curl_example'] = $this->buildCurlExample($endpoint);

        return response()->json($data);
    }

    /**
     * Met à jour un endpoint.
     */
    public function update(
        UpdateApplicationEndpointRequest $request,
        ApplicationEndpoint $applicationEndpoint
    ): JsonResponse {
        $applicationEndpoint->update(array_filter([
            'url'               => $request->url,
            'http_method'       => $request->http_method,
            'auth_headers'      => $request->has('auth_headers') ? $request->formattedHeaders() : null,
            'frequency_seconds' => $request->frequency_seconds,
        ], fn ($value) => $value !== null));

        // Si auth_headers est envoyé mais vide, on le met à null explicitement
        if ($request->has('auth_headers') && empty($request->input('auth_headers'))) {
            $applicationEndpoint->update(['auth_headers' => null]);
        }

        return response()->json([
            'message'  => 'Endpoint mis à jour.',
            'endpoint' => $this->formatEndpoint($applicationEndpoint->fresh()),
        ]);
    }

    /**
     * Supprime un endpoint.
     */
    public function destroy(ApplicationEndpoint $applicationEndpoint): JsonResponse
    {
        $applicationEndpoint->delete();

        return response()->json(['message' => 'Endpoint supprimé.']);
    }

    /**
     * Lance un check manuel (test) sur un endpoint.
     * Met à jour l'état ET retourne le détail pour affichage inline.
     */
    public function test(ApplicationEndpoint $applicationEndpoint): JsonResponse
    {
        $result = $this->healthChecker->check($applicationEndpoint);

        return response()->json([
            'message' => $result['success'] ? 'Endpoint reachable.' : 'Endpoint injoignable.',
            'result'  => [
                'success'         => $result['success'],
                'status'          => $result['status'],
                'status_code'     => $result['status_code'],
                'response_time_ms'=> $result['response_time_ms'],
                'body_preview'    => $result['body'],
                'error'           => $result['error'],
            ],
        ]);
    }

    /**
     * Test sans mise à jour (dry run).
     */
    public function dryTest(ApplicationEndpoint $applicationEndpoint): JsonResponse
    {
        $result = $this->healthChecker->dryRun($applicationEndpoint);

        return response()->json([
            'result' => [
                'success'         => $result['success'],
                'status'          => $result['status'],
                'status_code'     => $result['status_code'],
                'response_time_ms'=> $result['response_time_ms'],
                'body_preview'    => $result['body'],
                'error'           => $result['error'],
            ],
        ]);
    }

    // --- Helpers privés ---

    private function formatEndpoint(ApplicationEndpoint $ep): array
    {
        return [
            'id'                    => $ep->id,
            'application_id'        => $ep->application_id,
            'application_name'      => $ep->application?->name,
            'url'                   => $ep->url,
            'url_short'             => $this->shortenUrl($ep->url),
            'http_method'           => $ep->http_method,
            'frequency_seconds'     => $ep->frequency_seconds,
            'frequency_label'       => $this->formatFrequency($ep->frequency_seconds),
            'last_status'           => $ep->last_status,
            'last_response_time_ms' => $ep->last_response_time_ms,
            'last_checked_at'       => $ep->last_checked_at?->diffForHumans(),
            'has_headers'           => !empty($ep->auth_headers),
        ];
    }

    private function shortenUrl(string $url, int $max = 50): string
    {
        if (strlen($url) <= $max) {
            return $url;
        }

        $parsed = parse_url($url);
        $host   = $parsed['host'] ?? $url;
        $path   = $parsed['path'] ?? '';

        if (strlen($host . $path) <= $max) {
            return $host . $path;
        }

        return $host . '/…';
    }

    private function formatFrequency(int $seconds): string
    {
        return match (true) {
            $seconds < 60     => "{$seconds}s",
            $seconds < 3600   => floor($seconds / 60) . 'min',
            $seconds < 86400  => floor($seconds / 3600) . 'h',
            default            => floor($seconds / 86400) . 'j',
        };
    }

    /**
     * Génère un exemple cURL pour le panneau "Exemple de requête".
     */
    private function buildCurlExample(ApplicationEndpoint $ep): string
    {
        $lines   = [];
        $lines[] = "curl -X {$ep->http_method} \\";

        // URL
        $lines[] = "  '{$ep->url}' \\";

        // Headers
        $headers = $ep->auth_headers;
        if ($headers) {
            foreach ($headers as $key => $value) {
                $lines[] = "  -H '{$key}: {$value}' \\";
            }
        }

        // Retire le dernier backslash
        $lastIndex = count($lines) - 1;
        $lines[$lastIndex] = rtrim($lines[$lastIndex], ' \\');

        return implode("\n", $lines);
    }
}