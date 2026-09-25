<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AlertSettingController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationEndpointController;
use App\Http\Controllers\ApplicationGroupController;
use App\Http\Controllers\ApplicationMonitoringController;
use App\Http\Controllers\ApplicationTypeController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConnectorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MaintenanceWindowController;
use App\Http\Controllers\MonitoringComparisonController;
use App\Http\Controllers\MonitoringDashboardController;
use App\Http\Controllers\OwaspCategoryController;
use App\Http\Controllers\PlatformSettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ServerGroupController;
use App\Http\Controllers\ServerMonitoringController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SplashController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WebhookPageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (No Auth Required)
|--------------------------------------------------------------------------
*/

// Splash
Route::get('/', [SplashController::class, 'index'])->name('splash');

// Look
Route::get('/look', function () {
    return view('auth.look');
})->name('look');

// Auth
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset
Route::get('/forget', [AuthController::class, 'showForgetPassword'])->name('password.request');
Route::post('/forget', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::post('/resend-welcome', [AuthController::class, 'resendWelcomeLink'])->name('resend.welcome');

// Sign Up
Route::get('/sign', [AuthController::class, 'register'])->name('sign');
Route::post('/sign', [AuthController::class, 'store'])->name('sign.store');

// Language
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

// Webhook Receiver (Public for external systems)
Route::post('/webhooks/receive/{webhookId}', [WebhookController::class, 'receive'])->name('webhooks.receive');

/*
|--------------------------------------------------------------------------
| Protected Routes (Auth Required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/content', [DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::get('/users/{id}/delete', [UserController::class, 'delete'])->name('users.delete');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{id}/status', [UserController::class, 'changeStatus'])->name('users.status');

    // Servers
    Route::resource('server', ServerController::class)->except(['create']);
    Route::get('/server/{server}/delete', [ServerController::class, 'delete'])->name('servers.delete');
    Route::get('/servers/{id}/metrics', [ServerController::class, 'metrics'])->name('servers.metrics');
    Route::get('/dashboard/environment-chart', [ServerController::class, 'envChartData'])->name('dashboard.environment.chart');
    Route::patch('/server/{id}/status', [ServerController::class, 'changeStatus'])->name('server.status');
    Route::get('/monitoring/servers/{id}/history', [ServerMonitoringController::class, 'history'])->name('monitoring.servers.history');
    Route::get('/monitoring/servers/cpu-history', [ServerMonitoringController::class, 'globalCpuHistory'])->name('monitoring.servers.cpu-history');

    // Applications
    Route::resource('appli', ApplicationController::class)->except(['create']);
    Route::patch('/appli/{app}/status', [ApplicationController::class, 'changeStatus'])->name('appli.status');
    Route::get('/appli/{applications}/delete', [ApplicationController::class, 'delete'])->name('appli.delete');
    Route::get('/dashboard/application-environment-chart', [ApplicationController::class, 'environmentChartData'])->name('dashboard.application.environment.chart');
    Route::resource('application-types', ApplicationTypeController::class)->except(['create', 'show', 'edit']);
    Route::resource('application-groups', ApplicationGroupController::class)->except(['create', 'show', 'edit']);
    Route::post('/application-types', [ApplicationTypeController::class, 'store'])->name('application-types.store');
    Route::patch('/application-types/{applicationType}/activate', [ApplicationTypeController::class, 'activate'])->name('application-types.activate');
    Route::patch('/application-types/{applicationType}/deactivate', [ApplicationTypeController::class, 'deactivate'])->name('application-types.deactivate');
    Route::delete('/application-types/{applicationType}', [ApplicationTypeController::class, 'destroy'])->name('application-types.destroy');
    // Search
    Route::get('/search', [GlobalSearchController::class, 'index'])->name('global.search');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
    Route::get('/settings/platform', [PlatformSettingController::class, 'index'])->name('settings.platform.index');
    Route::put('/settings/platform', [PlatformSettingController::class, 'update'])->name('settings.platform.update');
    Route::get('/settings/connectors', [SettingController::class, 'connectors'])->name('settings.connectors.index');
    Route::get('/settings/audit-logs', [AuditLogController::class, 'index'])->name('settings.audit-logs.index');
    Route::get('/settings/alert-rules', [SettingController::class, 'alertRules'])->name('settings.alert-rules.index');
    Route::get('/settings/notifications', [SettingController::class, 'notifications'])->name('settings.notifications.index');
    
    // Settings - Maintenance
    Route::get('/settings/maintenance', [MaintenanceWindowController::class, 'index'])->name('settings.maintenance.index');
    Route::post('/settings/maintenance', [MaintenanceWindowController::class, 'store'])->name('settings.maintenance.store');
    Route::delete('/settings/maintenance/{id}', [MaintenanceWindowController::class, 'destroy'])->name('settings.maintenance.destroy');
    Route::put('/settings/update', [SettingController::class, 'update'])->name('settings.update');

    // Connectors
    Route::get('/connecteurs', [ConnectorController::class, 'index'])->name('connectors.index');
    Route::post('/connecteurs', [ConnectorController::class, 'store'])->name('connectors.store');
    Route::get('/connecteurs/{connector}/show', [ConnectorController::class, 'show'])->name('connectors.show');
    Route::get('/connecteurs/{connector}/edit', [ConnectorController::class, 'edit'])->name('connectors.edit');
    Route::put('/connecteurs/{connector}', [ConnectorController::class, 'update'])->name('connectors.update');
    Route::get('/connecteurs/{connector}/delete', [ConnectorController::class, 'delete'])->name('connectors.delete');
    Route::delete('/connecteurs/{connector}', [ConnectorController::class, 'destroy'])->name('connectors.destroy');
    Route::get('/connecteurs/{connector}/plug', [ConnectorController::class, 'plug'])->name('connectors.plug');
    Route::post('/connecteurs/{connector}/test', [ConnectorController::class, 'test'])->name('connectors.test');
    Route::get('/connecteurs/{connector}/edit-data', [ConnectorController::class, 'editData'])->name('connectors.edit-data');
    Route::post('/connecteurs/test-preview', [ConnectorController::class, 'testPreview'])->name('connectors.test-preview');
    Route::patch('/connecteurs/{id}/status', [ConnectorController::class, 'changeStatus'])->name('connectors.status');

    // API Keys
    Route::prefix('api-keys')->name('api-keys.')->group(function () {
        Route::get('/', [ApiKeyController::class, 'index'])->name('index');
        Route::post('/', [ApiKeyController::class, 'store'])->name('store');
        Route::post('{apiKey}/suspend', [ApiKeyController::class, 'suspend'])->name('suspend');
        Route::post('{apiKey}/regenerate', [ApiKeyController::class, 'regenerate'])->name('regenerate');
        Route::post('{apiKey}/revoke', [ApiKeyController::class, 'revoke'])->name('revoke');
        Route::delete('{apiKey}', [ApiKeyController::class, 'destroy'])->name('destroy');
    });

    // Endpoints
    Route::prefix('endpoints')->name('endpoints.')->group(function () {
        Route::get('/', [ApplicationEndpointController::class, 'index'])->name('index');
        Route::post('/', [ApplicationEndpointController::class, 'store'])->name('store');
        Route::get('{applicationEndpoint}', [ApplicationEndpointController::class, 'show'])->name('show');
        Route::put('{applicationEndpoint}', [ApplicationEndpointController::class, 'update'])->name('update');
        Route::delete('{applicationEndpoint}', [ApplicationEndpointController::class, 'destroy'])->name('destroy');
        Route::post('{applicationEndpoint}/test', [ApplicationEndpointController::class, 'test'])->name('test');
        Route::post('{applicationEndpoint}/dry-test', [ApplicationEndpointController::class, 'dryTest'])->name('dry-test');
    });

    // Webhooks
    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        Route::get('/', [WebhookController::class, 'index'])->name('index');
        Route::get('/event-types', [WebhookController::class, 'eventTypes'])->name('event-types');
        Route::post('/', [WebhookController::class, 'store'])->name('store');
        Route::get('{webhook}', [WebhookController::class, 'show'])->name('show');
        Route::put('{webhook}', [WebhookController::class, 'update'])->name('update');
        Route::delete('{webhook}', [WebhookController::class, 'destroy'])->name('destroy');
        Route::patch('{webhook}/status', [WebhookController::class, 'toggleStatus'])->name('status');
        Route::post('{webhook}/toggle', [WebhookController::class, 'toggleStatus'])->name('toggle');
        Route::post('{webhook}/error', [WebhookController::class, 'markError'])->name('mark-error');
        Route::post('{webhook}/rotate-secret', [WebhookController::class, 'rotateSecret'])->name('rotate-secret');
        Route::get('{webhook}/deliveries', [WebhookController::class, 'deliveries'])->name('deliveries');
        Route::get('{webhook}/deliveries/{delivery}', [WebhookController::class, 'deliveryDetail'])->name('delivery-detail');
        Route::post('{webhook}/deliveries/{delivery}/retry', [WebhookController::class, 'retryDelivery'])->name('retry-delivery');
    });
    Route::get('/web', [WebhookPageController::class, 'index'])->name('webhooks.page');
    Route::get('/webhooks/{webhook}/edit-data', [WebhookController::class, 'editData'])->name('webhooks.edit-data');

    // Monitoring
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        // Servers
        Route::get('/servers', [ServerMonitoringController::class, 'index'])->name('servers.index');
        Route::get('/servers/{id}', [ServerMonitoringController::class, 'show'])->name('servers.show');
        Route::get('/servers/{id}/metrics', [ServerMonitoringController::class, 'metrics'])->name('servers.metrics');
        Route::get('/servers/alert-history', [ServerMonitoringController::class, 'alertHistory'])->name('servers.alert-history');
        // Applications
        Route::get('/applications/{id}', [ApplicationMonitoringController::class, 'show'])->name('application.show');
        Route::get('/applications', [ApplicationMonitoringController::class, 'index'])->name('application.index');
        // Logs
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
        // Dashboard
        Route::get('/dashboard', [MonitoringDashboardController::class, 'index'])->name('dashboard');
        // Comparison
        Route::get('/compare', [MonitoringComparisonController::class, 'index'])->name('compare.index');
    });

    // Security
    Route::get('/security/compliance', [SecurityController::class, 'compliance'])->name('security.compliance');
    Route::get('/security/recommendations/{id?}', [SecurityController::class, 'recommendations'])->name('security.recommendations');

    // Alerts
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/check-critical', [AlertController::class, 'checkCritical'])->name('alerts.check-critical');
    Route::get('/alerts/{id}', [AlertController::class, 'show'])->name('alerts.show');
    Route::put('/alerts/{id}/acknowledge', [AlertController::class, 'acknowledge'])->name('alerts.acknowledge');
    Route::put('/alerts/{id}/assign', [AlertController::class, 'assign'])->name('alerts.assign');
    Route::put('/alerts/{id}/close', [AlertController::class, 'close'])->name('alerts.close');
    Route::put('/alerts/{id}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');
    Route::put('/alerts/{alert}/snooze', [AlertController::class, 'snooze'])->name('alerts.snooze');
    Route::post('/alerts/{alert}/comment', [AlertController::class, 'comment'])->name('alerts.comment');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{id}/download', [ReportController::class, 'download'])->name('reports.download');
    Route::get('/reports/statistics', [ReportController::class, 'statistics'])->name('reports.statistics');
    Route::get('/reports/generate-pdf', [ReportController::class, 'generatePdf'])->name('reports.generate-pdf');
    Route::get('/reports/generate-excel', [ReportController::class, 'generateExcel'])->name('reports.generate-excel');
    Route::get('/reports/generate-word', [ReportController::class, 'generateWord'])->name('reports.generate-word');

    // Groups
    Route::resource('server-groups', ServerGroupController::class)->only(['index', 'store', 'update']);
    Route::delete('/server-groups/{serverGroup}', [ServerGroupController::class, 'destroy'])->name('server-groups.destroy');
    Route::resource('application-groups', ApplicationGroupController::class)->except(['create', 'show', 'edit']);

    // Routes pour le CRUD OWASP Top 10
    Route::get('/security/owasp-categories', [OwaspCategoryController::class, 'index'])->name('owasp-categories.index');
    Route::post('/security/owasp-categories', [OwaspCategoryController::class, 'store'])->name('owasp-categories.store');
    Route::put('/security/owasp-categories/{owaspCategory}', [OwaspCategoryController::class, 'update'])->name('owasp-categories.update');
    Route::delete('/security/owasp-categories/{owaspCategory}', [OwaspCategoryController::class, 'destroy'])->name('owasp-categories.destroy');
    Route::patch('/security/owasp-categories/{owaspCategory}/toggle', [OwaspCategoryController::class, 'toggleActive'])->name('owasp-categories.toggle');
});