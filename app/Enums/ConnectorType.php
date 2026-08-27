<?php

namespace App\Enums;

enum ConnectorType: string
{
    case PROMETHEUS = 'prometheus';
    case WAZUH      = 'wazuh';

    public function label(): string
    {
        return match ($this) {
            self::PROMETHEUS => 'Prometheus',
            self::WAZUH      => 'Wazuh',
        };
    }

    public function defaultPort(): ?int
    {
        return match ($this) {
            self::PROMETHEUS => 9090,
            self::WAZUH      => 55000,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PROMETHEUS => 'mdi-chart-timeline-variant',
            self::WAZUH      => 'mdi-shield-lock',
        };
    }
}