<?php

use App\Http\Controllers\InboundWebhookController;
use App\Http\Controllers\Api\V1\ServerController;
use App\Http\Controllers\Api\V1\ApplicationController;
use App\Http\Controllers\Api\V1\AlertController;
use App\Http\Controllers\Api\V1\ReportController;
use Illuminate\Support\Facades\Route;

// =============================================================
// WEBHOOKS ENTRANTS (pas de clé API, vérification par signature)
// =============================================================
Route::post('/webhooks/inbound/{webhook}', InboundWebhookController::class)
    ->name('webhooks.inbound')
    ->middleware('webhook.verify');

// =============================================================
// API REST v1 — protégée par clé API
// =============================================================
Route::prefix('v1')->middleware('auth.api')->group(function () {

    // --- Serveurs ---
    Route::prefix('servers')->group(function () {
        Route::get('/',             [ServerController::class, 'index'])    ->middleware('scope:servers:read');
        Route::get('{server}',      [ServerController::class, 'show'])     ->middleware('scope:servers:read');
        Route::post('/',            [ServerController::class, 'store'])    ->middleware('scope:servers:write');
        Route::put('{server}',      [ServerController::class, 'update'])   ->middleware('scope:servers:write');
        Route::delete('{server}',   [ServerController::class, 'destroy'])  ->middleware('scope:servers:write');
    });

    // --- Applications ---
    Route::prefix('applications')->group(function () {
        Route::get('/',                 [ApplicationController::class, 'index'])    ->middleware('scope:applications:read');
        Route::get('{application}',      [ApplicationController::class, 'show'])     ->middleware('scope:applications:read');
        Route::post('/',                 [ApplicationController::class, 'store'])    ->middleware('scope:applications:write');
        Route::put('{application}',      [ApplicationController::class, 'update'])   ->middleware('scope:applications:write');
        Route::delete('{application}',   [ApplicationController::class, 'destroy'])  ->middleware('scope:applications:write');
    });

    // --- Alertes ---
    Route::prefix('alerts')->group(function () {
        Route::get('/',           [AlertController::class, 'index'])    ->middleware('scope:alerts:read');
        Route::get('{alert}',     [AlertController::class, 'show'])     ->middleware('scope:alerts:read');
        Route::post('/',          [AlertController::class, 'store'])    ->middleware('scope:alerts:write');
        Route::put('{alert}',     [AlertController::class, 'update'])   ->middleware('scope:alerts:write');
    });

    // --- Rapports ---
    Route::prefix('reports')->group(function () {
        Route::get('/',           [ReportController::class, 'index'])    ->middleware('scope:reports:read');
        Route::get('{report}',    [ReportController::class, 'show'])     ->middleware('scope:reports:read');
        Route::post('/',          [ReportController::class, 'store'])    ->middleware('scope:reports:write');
    });
Route::post('/webhooks/receive/{webhook}', [WebhookController::class, 'receive'])->name('webhooks.receive');
});