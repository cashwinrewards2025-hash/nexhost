<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Monitoring\MonitoringController;
use App\Http\Controllers\API\Billing\BillingController;
use App\Http\Controllers\API\Reports\ReportController;
use App\Http\Controllers\API\Core\ServerController;

Route::middleware(['auth:sanctum', 'api'])->group(function () {
    // Monitoring Routes
    Route::prefix('monitoring')->group(function () {
        Route::get('servers/{server}/metrics', [MonitoringController::class, 'getMetrics']);
        Route::post('servers/{server}/metrics', [MonitoringController::class, 'recordMetric']);
        Route::get('servers/{server}/health-score', [MonitoringController::class, 'getHealthScore']);
        Route::get('servers/{server}/cpu', [MonitoringController::class, 'getCPUMetrics']);
        Route::get('servers/{server}/memory', [MonitoringController::class, 'getMemoryMetrics']);
        Route::get('servers/{server}/disk', [MonitoringController::class, 'getDiskMetrics']);
    });

    // Billing Routes
    Route::prefix('billing')->group(function () {
        Route::post('clients/{client}/invoices', [BillingController::class, 'generateInvoice']);
        Route::get('invoices/{invoice}', [BillingController::class, 'getInvoice']);
        Route::get('clients/{client}/invoices', [BillingController::class, 'getClientInvoices']);
        Route::post('invoices/{invoice}/payments', [BillingController::class, 'recordPayment']);
        Route::get('clients/{client}/payment-due', [BillingController::class, 'getPaymentDue']);
    });

    // Report Routes
    Route::prefix('reports')->group(function () {
        Route::post('servers/{server}/generate', [ReportController::class, 'generateReport']);
        Route::get('reports/{report}', [ReportController::class, 'getReport']);
        Route::get('servers/{server}/reports', [ReportController::class, 'listReports']);
        Route::get('reports/{report}/pdf', [ReportController::class, 'downloadPDF']);
        Route::post('verify', [ReportController::class, 'verifyReport']);
    });

    // Server Routes
    Route::prefix('servers')->group(function () {
        Route::get('clients/{client}/servers', [ServerController::class, 'listServers']);
        Route::get('{server}', [ServerController::class, 'getServer']);
        Route::post('clients/{client}/servers', [ServerController::class, 'createServer']);
        Route::put('{server}', [ServerController::class, 'updateServer']);
        Route::delete('{server}', [ServerController::class, 'deleteServer']);
    });
});

// Public Routes
Route::post('auth/login', 'App\Http\Controllers\API\Auth\AuthController@login');
Route::post('auth/register', 'App\Http\Controllers\API\Auth\AuthController@register');
Route::get('reports/verify/{token}', [ReportController::class, 'verifyReport']);
