<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parsed = parse_url($value);
        $host = strtolower($parsed['host'] ?? '');

        $blocked = ['localhost', '127.0.0.1', '::1', '0.0.0.0', '169.254.169.254', 'metadata.google.internal', '100.100.100.200'];

        if (in_array($host, $blocked)) {
            $fail("L'URL ne peut pas pointer vers une adresse locale ou un service cloud interne.");
            return;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $fail("L'URL ne peut pas pointer vers une adresse réseau privée.");
                return;
            }
        }

        foreach (['.local', '.internal', '.localhost', '.test'] as $suffix) {
            if (str_ends_with($host, $suffix)) {
                $fail("L'URL ne peut pas pointer vers un domaine interne.");
                return;
            }
        }
    }
}