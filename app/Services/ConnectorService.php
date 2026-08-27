<?php

namespace App\Services;

use App\Models\Connector;
use App\Services\Connectors\ConnectorTesterFactory;
use Illuminate\Support\Facades\DB;

class ConnectorService
{
    /**
     * Crée un connecteur et le lie à l'utilisateur.
     */
    public function create(array $data, string $userId): Connector
    {
        return DB::transaction(function () use ($data, $userId) {
            return Connector::create([
                ...$data,
                'status'     => 'never_tested',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        });
    }

    /**
     * Met à jour un connecteur.
     */
    public function update(Connector $connector, array $data, string $userId): Connector
    {
        return DB::transaction(function () use ($connector, $data, $userId) {
            // Si on change l'URL ou l'auth, on remet le statut à "never_tested"
            $sensitiveFields = ['base_url', 'auth_username', 'auth_password_encrypted', 'api_port'];
            $shouldResetStatus = false;

            foreach ($sensitiveFields as $field) {
                if (array_key_exists($field, $data) && $data[$field] !== $connector->getOriginal($field)) {
                    $shouldResetStatus = true;
                    break;
                }
            }

            if ($shouldResetStatus) {
                $data['status']             = 'never_tested';
                $data['last_check_at']      = null;
                $data['last_success_at']    = null;
                $data['last_error_message'] = null;
            }

            $connector->update([
                ...$data,
                'updated_by' => $userId,
            ]);

            return $connector->fresh();
        });
    }

    /**
     * Supprime un connecteur.
     */
    public function delete(Connector $connector): void
    {
        $connector->delete();
    }

    /**
     * Teste la connexion d'un connecteur et met à jour son statut.
     */
    public function testConnection(Connector $connector): object
    {
        $tester = ConnectorTesterFactory::make($connector);
        $result = $tester->test();

        if ($result->success) {
            $connector->markAsConnected();
        } else {
            $connector->markAsError($result->message);
        }

        return (object) [
            'success'       => $result->success,
            'message'       => $result->message,
            'response_time' => $result->responseTimeMs,
            'metadata'      => $result->metadata,
            'status'        => $connector->fresh()->status,
        ];
    }
}