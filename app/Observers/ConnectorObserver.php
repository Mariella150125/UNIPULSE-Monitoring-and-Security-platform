<?php

namespace App\Observers;

use App\Models\Connector;
use Illuminate\Support\Facades\Crypt;

class ConnectorObserver
{
    /**
     * Avant création : chiffrer le mot de passe s'il est fourni en clair.
     */
    public function creating(Connector $connector): void
    {
        $this->handlePasswordEncryption($connector);
    }

    /**
     * Avant mise à jour : chiffrer le mot de passe s'il a été modifié.
     */
    public function updating(Connector $connector): void
    {
        $this->handlePasswordEncryption($connector);
    }

    /**
     * Logique partagée de chiffrement.
     *
     * Convention : si la valeur passe par setAttribute et n'est PAS
     * déjà au format "base64:" (format Laravel Crypt), on la chiffre.
     */
    private function handlePasswordEncryption(Connector $connector): void
    {
        $password = $connector->getAttributes()['auth_password_encrypted'] ?? null;

        if ($password === null || $password === '') {
            return;
        }

        // Si déjà chiffré par Laravel Crypt → ne pas double-chiffrer
        if (str_starts_with($password, 'base64:')) {
            return;
        }

        // Sinon c'est du texte clair → chiffrer
        $connector->auth_password_encrypted = Crypt::encryptString($password);
    }
}