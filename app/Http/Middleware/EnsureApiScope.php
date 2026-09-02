<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiScope
{
    /**
     * Vérifie que la clé API authentifiée possède les scopes requis.
     *
     * Usage sur la route :
     *   ->middleware('scope:servers:read')
     *   ->middleware('scope:servers:read,applications:write')  // OU logique
     */
    public function handle(Request $request, Closure $next, string ...$scopes): Response
    {
        /** @var ApiKey|null $apiKey */
        $apiKey = $request->attributes->get('api_key');

        if (!$apiKey) {
            return response()->json([
                'error' => [
                    'code'    => 401,
                    'message' => 'Authentification requise.',
                ],
            ], 401);
        }

        // Au moins un des scopes demandés doit être présent (OU logique)
        $hasAnyScope = false;

        foreach ($scopes as $scope) {
            [$resource, $action] = explode(':', $scope, 2);

            $matched = $apiKey->scopes()
                ->where('resource', $resource)
                ->where('action', $action)
                ->exists();

            if ($matched) {
                $hasAnyScope = true;
                break;
            }
        }

        if (!$hasAnyScope) {
            return response()->json([
                'error' => [
                    'code'    => 403,
                    'message' => 'Permissions insuffisantes. Scopes requis : ' . implode(' OU ', $scopes),
                    'required' => $scopes,
                    'owned'    => $apiKey->scope_list,
                ],
            ], 403);
        }

        return $next($request);
    }
}
