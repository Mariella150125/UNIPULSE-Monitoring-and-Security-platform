<?php

namespace App\Services\Connectors;

readonly class ConnectorTestResult
{
    public function __construct(
        public bool   $success,
        public string $message = '',
        public ?float $responseTimeMs = null,
        public ?array $metadata = null,
    ) {}

    public static function ok(string $message = 'Connexion réussie.', ?float $ms = null, ?array $meta = null): self
    {
        return new self(success: true, message: $message, responseTimeMs: $ms, metadata: $meta);
    }

    public static function fail(string $message, ?float $ms = null): self
    {
        return new self(success: false, message: $message, responseTimeMs: $ms);
    }
}