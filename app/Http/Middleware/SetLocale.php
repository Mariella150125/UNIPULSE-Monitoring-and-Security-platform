<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', 'fr');

        if (!in_array($locale, ['fr', 'en'])) {
            $locale = 'fr';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}