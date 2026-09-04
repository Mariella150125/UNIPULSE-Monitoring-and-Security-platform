<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WebhookEventTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // --- Plateforme (outbound) ---
            ['code' => 'alert.created',           'label' => 'Alerte créée',               'direction' => 'outbound', 'description' => 'Une nouvelle alerte a été générée'],
            ['code' => 'alert.escalated',         'label' => 'Alerte escaladée',            'direction' => 'outbound', 'description' => 'Une alerte a changé de niveau de sévérité'],
            ['code' => 'alert.resolved',          'label' => 'Alerte résolue',              'direction' => 'outbound', 'description' => 'Une alerte a été marquée comme résolue'],
            ['code' => 'incident.triggered',      'label' => 'Incident déclenché',          'direction' => 'outbound', 'description' => 'Un incident a été ouvert automatiquement'],
            ['code' => 'incident.resolved',       'label' => 'Incident résolu',             'direction' => 'outbound', 'description' => 'Un incident a été clôturé'],
            ['code' => 'report.generated',        'label' => 'Rapport généré',              'direction' => 'outbound', 'description' => 'Un rapport planifié a été produit'],
            ['code' => 'user.login',              'label' => 'Connexion utilisateur',       'direction' => 'outbound', 'description' => 'Un utilisateur s\'est connecté'],
            ['code' => 'system.update',           'label' => 'Mise à jour système',         'direction' => 'outbound', 'description' => 'La plateforme a été mise à jour'],

            // --- Application (outbound, filet de secours) ---
            ['code' => 'application.health_failed','label' => 'Santé applicative défaillante','direction' => 'outbound', 'description' => 'Le PULL d\'une application a échoué consécutivement'],

            // --- Wazuh / connecteur (inbound) ---
            ['code' => 'wazuh.vulnerability',     'label' => 'Vulnérabilité Wazuh',         'direction' => 'inbound',  'description' => 'Événement de vulnérabilité reçu de Wazuh'],
            ['code' => 'wazuh.alert',             'label' => 'Alerte Wazuh',                'direction' => 'inbound',  'description' => 'Alerte de sécurité reçue de Wazuh'],
            ['code' => 'wazuh.sca',               'label' => 'Conformité SCA Wazuh',        'direction' => 'inbound',  'description' => 'Résultat de vérification SCA reçu'],

            // --- Les deux directions ---
            ['code' => 'ping',                    'label' => 'Ping',                        'direction' => 'both',     'description' => 'Vérification de connectivité du webhook'],
        ];

        foreach ($types as $type) {
            DB::table('webhook_event_types')->insert([
                'code'                  => $type['code'],
                'label'                 => $type['label'],
                'applicable_direction'  => $type['direction'],
                'description'           => $type['description'],
            ]);
        }
    }
}
