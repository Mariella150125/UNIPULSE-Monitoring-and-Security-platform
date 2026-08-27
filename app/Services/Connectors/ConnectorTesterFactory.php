<?php

namespace App\Services\Connectors;

use App\Enums\ConnectorType;
use App\Models\Connector;

class ConnectorTesterFactory
{
    public static function make(Connector $connector): BaseConnectorTester
    {
        return match ($connector->type) {
            ConnectorType::PROMETHEUS->value => new PrometheusTester($connector),
            ConnectorType::WAZUH->value      => new WazuhTester($connector),
            default => throw new \InvalidArgumentException("Type de connecteur inconnu : {$connector->type}"),
        };
    }
}