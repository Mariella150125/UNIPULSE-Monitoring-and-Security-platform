<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\ApiKey;
use Illuminate\Http\Request;

class LogIngestController extends Controller
{
    public function store(Request $request)
    {
        try {
            // 1. Sécurité : On utilise la méthode native de ton modèle ApiKey
            $token = $request->bearerToken();
            $apiKey = ApiKey::authenticate($token);
            
            if (!$apiKey) {
                return response()->json(['error' => 'Unauthorized. Invalid or expired API Key.'], 401);
            }

            // 2. Récupération des logs
            $incomingLogs = $request->input('logs', []);
            if (empty($incomingLogs) && $request->has('message')) {
                $incomingLogs = [$request->all()];
            }

            $savedCount = 0;
            foreach ($incomingLogs as $logData) {
                if (!isset($logData['source'], $logData['level'], $logData['message'])) {
                    continue;
                }

                // 3. Enregistrement
                Log::create([
                    'application_id' => $logData['application_id'] ?? null,
                    'source'         => $logData['source'],
                    'level'          => strtoupper($logData['level']),
                    'message'        => $logData['message'],
                    'ip_address'     => $request->ip(),
                ]);
                $savedCount++;
            }

            return response()->json(['success' => true, 'message' => $savedCount . ' logs ingérés avec succès.'], 201);

        } catch (\Exception $e) {
            // Si Laravel plante, on attrape l'erreur et on la renvoie
            return response()->json([
                'error' => 'Server Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}