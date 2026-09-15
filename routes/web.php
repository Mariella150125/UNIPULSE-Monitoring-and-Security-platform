
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationTypeController;
use App\Http\Controllers\ServerGroupController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\ConnectorController;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\PlatformSettingController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ServerMonitoringController;
use App\Http\Controllers\ApplicationMonitoringController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MonitoringDashboardController;
use App\Http\Controllers\ApplicationGroupController;
use App\Http\Controllers\MonitoringComparisonController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AlertSettingController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\MaintenanceWindowController;
use App\Http\Controllers\ReportController;

// 1. Afficher la page (GET)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// 2. Traiter le bouton Se connecter (POST)
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// 3. Déconnexion
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 4. Le dashboard (protégé)
Route::get('/content', function () {
    return view('layout/dashboard');
})->middleware('auth')->name('dashboard');

Route::get('/forget', [AuthController::class, 'showForgetPassword'])
    ->name('password.request');
Route::post('/forget', [AuthController::class, 'sendResetLink'])
    ->name('password.email');
Route::get('/password/{token}', [AuthController::class, 'showResetPassword'])
    ->name('password.reset');
Route::post('/password', [AuthController::class, 'resetPassword'])
    ->name('password.update');
Route::post('/resend-welcome', [AuthController::class, 'resendWelcomeLink'])->name('resend.welcome');
// créer un user
Route::get('/sign', [AuthController::class, 'register'])->name('sign');
Route::post('/sign', [AuthController::class, 'store'])->name('sign.store');
Route::patch('/users/{id}/status', [UserController::class, 'changeStatus'])->name('users.status');

Route::get('/look', function () {
    return view('auth.look');
})->name('look');




// CRUD USERS

Route::get('/users', [UserController::class, 'index'])
    ->middleware('auth')
    ->name('users.index');

Route::get('/users/{id}', [UserController::class, 'show'])
    ->middleware('auth')
    ->name('users.show');

Route::get('/users/{id}/edit', [UserController::class, 'edit'])
    ->middleware('auth')
    ->name('users.edit');

Route::put('/users/{id}', [UserController::class, 'update'])
    ->middleware('auth')
    ->name('users.update');

Route::get('/users/{id}/delete', [UserController::class, 'delete'])
    ->middleware('auth')
    ->name('users.delete');

Route::delete('/users/{id}', [UserController::class, 'destroy'])
    ->middleware('auth')
    ->name('users.destroy');

// gestion des serveurs 

Route::resource('server', ServerController::class)
    ->except(['create']);
Route::get('/server/{server}/delete', [ServerController::class, 'delete'])->name('servers.delete');
Route::get('/servers/{id}/metrics', [ServerController::class, 'metrics'])->name('servers.metrics');
Route::get('/dashboard/environment-chart', [ServerController::class, 'envChartData'])
    ->name('dashboard.environment.chart');
Route::patch('/server/{id}/status', [ServerController::class, 'changeStatus'])->name('server.status');
// applications 
Route::resource('appli', ApplicationController::class)
    ->except(['create'])
    ->middleware('auth');
Route::patch('/appli/{app}/status', [ApplicationController::class, 'changeStatus'])->name('appli.status');
Route::get('/appli/{applications}/delete', [ApplicationController::class, 'delete'])->name('appli.delete');
Route::resource(
    'application-types',
    ApplicationTypeController::class
)->except(['create', 'show', 'edit']);
Route::get(
    '/dashboard/application-environment-chart',
    [ApplicationController::class, 'environmentChartData']
)->name('dashboard.application.environment.chart');
Route::get(
    '/search',
    [GlobalSearchController::class, 'index']
)->name('global.search');

Route::get('/profile', function () {
    return view('layout.profile');
})->name('profile');

Route::get('/settings', function () {
    return view('layout.settings');
})->name('settings');

Route::resource(
    'server-groups',
    ServerGroupController::class
)->only([
    'index',
    'store',
    'update',
]);


Route::get('/language/{locale}', function ($locale) {

    if (!in_array($locale, ['fr', 'en'])) {
        abort(404);
    }

    session(['locale' => $locale]);

    return redirect()->back();

})->name('language');

Route::post('/language/change', function (Request $request) {

    $language = $request->input('language');

    if (in_array($language, ['fr', 'en'])) {
        session(['locale' => $language]);
    }

    return back();

})->name('language.change');



Route::middleware(['auth'])->group(function () {
    Route::get('/connecteurs', [ConnectorController::class, 'index'])->name('connectors.index');
    Route::post('/connecteurs', [ConnectorController::class, 'store'])->name('connectors.store');
    Route::get('/connecteurs/{connector}/show', [ConnectorController::class, 'show'])->name('connectors.show');
    Route::get('/connecteurs/{connector}/edit', [ConnectorController::class, 'edit'])->name('connectors.edit');
    Route::put('/connecteurs/{connector}', [ConnectorController::class, 'update'])->name('connectors.update');
    Route::get('/connecteurs/{connector}/delete', [ConnectorController::class, 'delete'])->name('connectors.delete');
    Route::delete('/connecteurs/{connector}', [ConnectorController::class, 'destroy'])->name('connectors.destroy');
    Route::get('/connecteurs/{connector}/plug', [ConnectorController::class, 'plug'])->name('connectors.plug');
    Route::post('/connecteurs/{connector}/test', [ConnectorController::class, 'test'])->name('connectors.test')
        ->middleware('throttle.connector.test');
    Route::get('/connecteurs/{connector}/edit-data', [ConnectorController::class, 'editData'])->name('connectors.edit-data');
    Route::post('/connecteurs/test-preview', [ConnectorController::class, 'testPreview'])->name('connectors.test-preview');
    Route::patch('/connecteurs/{id}/status', [ConnectorController::class, 'changeStatus'])->name('connectors.status');
});


Route::prefix('api-keys')->name('api-keys.')->group(function () {
    Route::get('/',               [ApiKeyController::class, 'index'])          ->name('index');
    Route::post('/',              [ApiKeyController::class, 'store'])          ->name('store');
    Route::post('{apiKey}/suspend',   [ApiKeyController::class, 'suspend'])    ->name('suspend');
    Route::post('{apiKey}/regenerate',[ApiKeyController::class, 'regenerate']) ->name('regenerate');
    Route::post('{apiKey}/revoke',    [ApiKeyController::class, 'revoke'])     ->name('revoke');
    Route::delete('{apiKey}',         [ApiKeyController::class, 'destroy'])    ->name('destroy');
});

use App\Http\Controllers\ApplicationEndpointController;

Route::prefix('endpoints')->name('endpoints.')->group(function () {
    Route::get('/',                          [ApplicationEndpointController::class, 'index'])          ->name('index');
    Route::post('/',                         [ApplicationEndpointController::class, 'store'])          ->name('store');
    Route::get('{applicationEndpoint}',      [ApplicationEndpointController::class, 'show'])           ->name('show');
    Route::put('{applicationEndpoint}',      [ApplicationEndpointController::class, 'update'])         ->name('update');
    Route::delete('{applicationEndpoint}',   [ApplicationEndpointController::class, 'destroy'])        ->name('destroy');
    Route::post('{applicationEndpoint}/test',[ApplicationEndpointController::class, 'test'])           ->name('test');
    Route::post('{applicationEndpoint}/dry-test', [ApplicationEndpointController::class, 'dryTest'])   ->name('dry-test');
});

use App\Http\Controllers\WebhookController;

Route::prefix('webhooks')->name('webhooks.')->group(function () {
    // Liste, stats, et types d'événements pour les formulaires
    Route::get('/',                    [WebhookController::class, 'index'])             ->name('index');
    Route::get('/event-types',         [WebhookController::class, 'eventTypes'])        ->name('event-types');

    // CRUD
    Route::post('/',                   [WebhookController::class, 'store'])             ->name('store');
    Route::get('{webhook}',            [WebhookController::class, 'show'])              ->name('show');
    Route::put('{webhook}',            [WebhookController::class, 'update'])            ->name('update');
    Route::delete('{webhook}',         [WebhookController::class, 'destroy'])           ->name('destroy');

    // Actions spécifiques
    Route::patch('{webhook}/status',   [WebhookController::class, 'toggleStatus'])     ->name('status');
    Route::post('{webhook}/toggle',    [WebhookController::class, 'toggleStatus'])      ->name('toggle');
    Route::post('{webhook}/error',     [WebhookController::class, 'markError'])         ->name('mark-error');
    Route::post('{webhook}/rotate-secret', [WebhookController::class, 'rotateSecret'])  ->name('rotate-secret');

    // Historique des livraisons
    Route::get('{webhook}/deliveries',            [WebhookController::class, 'deliveries'])       ->name('deliveries');
    Route::get('{webhook}/deliveries/{delivery}', [WebhookController::class, 'deliveryDetail'])   ->name('delivery-detail');
    Route::post('{webhook}/deliveries/{delivery}/retry', [WebhookController::class, 'retryDelivery'])->name('retry-delivery');
    // La route de réception

});
Route::post('/webhooks/receive/{webhookId}', [WebhookController::class, 'receive'])->name('webhooks.receive');
use App\Http\Controllers\WebhookPageController;

Route::get('/web', [WebhookPageController::class, 'index'])
->middleware('auth')
->name('webhooks.page');
Route::get('/webhooks/{webhook}/edit-data', [WebhookController::class, 'editData'])->name('webhooks.edit-data');

Route::get('/settings/platform', [PlatformSettingController::class, 'index'])->name('settings.platform.index');
Route::put('/settings/platform', [PlatformSettingController::class, 'update'])->name('settings.platform.update');
Route::get('/settings/connectors', [SettingController::class, 'connectors'])->name('settings.connectors.index');
Route::get('/settings/audit-logs', [AuditLogController::class, 'index'])->name('settings.audit-logs.index');

use App\Http\Controllers\ProfileController;
// partie profil
// Afficher le profil
Route::get('/profile', function () {
    return view('layout.profile');
})->middleware('auth')->name('profile');

// Mettre à jour les infos
Route::put('/profile', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');

// Mettre à jour le mot de passe
Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('auth')->name('profile.password.update');

use App\Http\Controllers\SplashController;

Route::get('/', [SplashController::class, 'index'])
    ->name('splash');

  //MONITORING
Route::prefix('monitoring')->name('monitoring.')->middleware('auth')->group(function () {
    
    // Sous-module Serveurs
    Route::get('/servers', [ServerMonitoringController::class, 'index'])->name('servers.index');
    Route::get('/servers/{id}', [ServerMonitoringController::class, 'show'])->name('servers.show');
    Route::get('/servers/{id}/metrics', [ServerMonitoringController::class, 'metrics'])->name('servers.metrics');
    
    // sous-module application
    Route::get('/applications/{id}', [ApplicationMonitoringController::class, 'show'])->name('application.show');
    Route::get('/applications', [ApplicationMonitoringController::class, 'index'])->name('application.index');
    // sous-module log
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    // dashboard
    Route::get('/dashboard', [MonitoringDashboardController::class, 'index'])->name('dashboard');
    // Comparaison
     Route::get('/compare', [MonitoringComparisonController::class, 'index'])->name('compare.index');
});

Route::resource('application-groups', ApplicationGroupController::class)->except(['create', 'show', 'edit']);
Route::get('/monitoring/apps', function () {
    return view('coming-soon');
});

Route::get('/security/compliance', [SecurityController::class, 'compliance'])->name('security.compliance');
Route::get('/security/recommendations', [SecurityController::class, 'recommendations'])->name('security.recommendations');


Route::get('/security/vulnerabilities', function () {
    return view('coming-soon');
});
 
// Route pour afficher la liste des alertes (celle qui manque actuellement)
Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');

// Route pour vérifier les alertes critiques (pour le son et le clignotement)
Route::get('/alerts/check-critical', [AlertController::class, 'checkCritical'])->name('alerts.check-critical');
Route::get('/alerts/{id}', [AlertController::class, 'show'])->name('alerts.show');

// LES NOUVELLES ROUTES D'ACTION :
Route::put('/alerts/{id}/acknowledge', [AlertController::class, 'acknowledge'])->name('alerts.acknowledge');
Route::put('/alerts/{id}/assign', [AlertController::class, 'assign'])->name('alerts.assign');
Route::put('/alerts/{id}/close', [AlertController::class, 'close'])->name('alerts.close');Route::get('/settings/alert-rules', [AlertSettingController::class, 'alertRules'])->name('settings.alert-rules.index');
Route::get('/settings/notifications', [AlertSettingController::class, 'notifications'])->name('settings.notifications.index');
Route::get('/settings/maintenance', [AlertSettingController::class, 'maintenance'])->name('settings.maintenance.index');

// Affichage des pages
Route::get('/settings/alert-rules', [SettingController::class, 'alertRules'])->name('settings.alert-rules.index');
Route::get('/settings/notifications', [SettingController::class, 'notifications'])->name('settings.notifications.index');
Route::get('/settings/maintenance', [MaintenanceWindowController::class, 'index'])->name('settings.maintenance.index');
Route::post('/settings/maintenance', [MaintenanceWindowController::class, 'store'])->name('settings.maintenance.store');
Route::delete('/settings/maintenance/{id}', [MaintenanceWindowController::class, 'destroy'])->name('settings.maintenance.destroy');

// La route POST qui sauvegarde (pointe vers l'action du formulaire)
Route::post('/settings/update', [SettingController::class, 'update'])->name('settings.update');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
Route::get('/reports/{id}/download', [ReportController::class, 'download'])->name('reports.download');
Route::get('/reports/statistics', [ReportController::class, 'statistics'])->name('reports.statistics');
Route::get('/reports/generate-pdf', [ReportController::class, 'generatePdf'])->name('reports.generate-pdf');
Route::get('/reports/generate-excel', [ReportController::class, 'generateExcel'])->name('reports.generate-excel');
Route::get('/reports/generate-word', [ReportController::class, 'generateWord'])->name('reports.generate-word');



