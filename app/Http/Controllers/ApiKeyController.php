<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApiKeyRequest;
use App\Models\ApiKey;
use App\Models\ApiRequestLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiKeyController extends Controller
{
    /**
     * Liste toutes les clés API de l'utilisateur avec leurs scopes.
     */
    public function index(Request $request): JsonResponse
    {
        $keys = ApiKey::where('user_id', $request->user()->id)
            ->with('scopes')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($key) {
                return [
                    'id'             => $key->id,
                    'name'           => $key->name,
                    'key_prefix'     => $key->key_prefix,
                    'status'         => $key->status,
                    'is_expired'     => $key->is_expired,
                    'is_usable'      => $key->is_usable,
                    'scopes'         => $key->scope_list,
                    'last_used_at'   => $key->last_used_at?->diffForHumans(),
                    'last_used_ip'   => $key->last_used_ip,
                    'expires_at'     => $key->expires_at?->format('d/m/Y'),
                    'created_at'     => $key->created_at->format('d/m/Y'),
                ];
            });

        // Stats pour le panneau sécurité
        $stats = [
            'active_count'    => $keys->where('status', 'active')->where('is_expired', false)->count(),
            'suspended_count' => $keys->where('status', 'suspended')->count(),
            'revoked_count'   => $keys->where('status', 'revoked')->count(),
            'expired_count'   => $keys->where('is_expired', true)->count(),
        ];

        // Taux d'authentification sur 24h
        $totalRequests  = ApiRequestLog::where('requested_at', '>=', now()->subDay())->count();
        $blockedRequests = ApiRequestLog::where('requested_at', '>=', now()->subDay())
            ->where('status_code', 401)
            ->count();

        $stats['total_requests_24h']  = $totalRequests;
        $stats['blocked_requests_24h'] = $blockedRequests;
        $stats['auth_success_rate']    = $totalRequests > 0
            ? round((($totalRequests - $blockedRequests) / $totalRequests) * 100, 1)
            : 100;

        return response()->json([
            'keys'  => $keys,
            'stats' => $stats,
        ]);
    }

    /**
     * Génère une nouvelle clé API.
     * Retourne la clé en clair UNE SEULE FOIS.
     */
    public function store(StoreApiKeyRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            /** @var ApiKey $key */
            [$key, $plainKey] = ApiKey::generate(
                name:      $request->name,
                userId:    $request->user()->id,
                expiresAt: $request->expires_at,
            );

            // Création des scopes
            foreach ($request->scopes as $scope) {
                [$resource, $action] = explode(':', $scope);
                $key->scopes()->create([
                    'resource' => $resource,
                    'action'   => $action,
                ]);
            }

            return response()->json([
                'message'   => 'Clé API générée avec succès.',
                'key_id'    => $key->id,
                'plain_key' => $plainKey,     // ← jamais renvoyé par un autre endpoint
                'key'       => [
                    'id'          => $key->id,
                    'name'        => $key->name,
                    'key_prefix'  => $key->key_prefix,
                    'scopes'      => $key->scope_list,
                    'expires_at'  => $key->expires_at?->format('d/m/Y'),
                    'created_at'  => $key->created_at->format('d/m/Y'),
                ],
            ], 201);
        });
    }

    /**
     * Suspend / reprend une clé (toggle).
     */
    public function suspend(ApiKey $apiKey): JsonResponse
    {
        $this->authorizeKey($apiKey);

        if ($apiKey->status === 'suspended') {
            $apiKey->resume();
            $action = 'réactivée';
        } else {
            $apiKey->suspend();
            $action = 'suspendue';
        }

        return response()->json([
            'message' => "Clé « {$apiKey->name} » {$action}.",
            'status'  => $apiKey->fresh()->status,
        ]);
    }

    /**
     * Régénère la clé (nouveau hash, ancienne clé invalidée).
     * Retourne la nouvelle clé en clair UNE SEULE FOIS.
     */
    public function regenerate(ApiKey $apiKey): JsonResponse
    {
        $this->authorizeKey($apiKey);

        if ($apiKey->status === 'revoked') {
            return response()->json(['message' => 'Impossible de régénérer une clé révoquée.'], 409);
        }

        $plainKey = $apiKey->regenerate();

        return response()->json([
            'message'   => "Clé « {$apiKey->name} » régénérée. L'ancienne clé est invalide.",
            'plain_key' => $plainKey,
            'key_prefix'=> $apiKey->key_prefix,
        ]);
    }

    /**
     * Révoque définitivement une clé.
     */
    public function revoke(ApiKey $apiKey): JsonResponse
    {
        $this->authorizeKey($apiKey);

        $apiKey->revoke();

        return response()->json([
            'message' => "Clé « {$apiKey->name} » révoquée définitivement.",
        ]);
    }

    /**
     * Supprime une clé (soft : révoque + suppression si tu veux, ici on révoque uniquement).
     */
    public function destroy(ApiKey $apiKey): JsonResponse
    {
        $this->authorizeKey($apiKey);

        $apiKey->revoke();
        $apiKey->delete(); // soft delete si tu l'actives, sinon hard delete

        return response()->json(['message' => 'Clé supprimée.']);
    }

    // --- Privé ---

    private function authorizeKey(ApiKey $apiKey): void
    {
        if ($apiKey->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé à cette clé.');
        }
    }
}