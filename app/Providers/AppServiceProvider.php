<?php

namespace App\Providers;

use App\Models\Connector;
use App\Observers\ConnectorObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Connector::observe(ConnectorObserver::class);
        Gate::policy(Connector::class, \App\Policies\ConnectorPolicy::class);
    }
}