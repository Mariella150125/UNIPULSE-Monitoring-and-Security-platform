<?php

namespace App\Enums;

enum ConnectorStatus: string
{
    case CONNECTED    = 'connected';
    case ERROR        = 'error';
    case NEVER_TESTED = 'never_tested';

    public function label(): string
    {
        return match ($this) {
            self::CONNECTED    => 'Connecté',
            self::ERROR        => 'En erreur',
            self::NEVER_TESTED => 'Jamais testé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CONNECTED    => 'success',
            self::ERROR        => 'danger',
            self::NEVER_TESTED => 'neutral',
        };
    }

    public function dotColor(): string
    {
        return match ($this) {
            self::CONNECTED    => 'bg-emerald-500',
            self::ERROR        => 'bg-red-500',
            self::NEVER_TESTED => 'bg-gray-400',
        };
    }
}