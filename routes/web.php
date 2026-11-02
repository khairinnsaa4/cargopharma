<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AuditLogController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ExceptionController;
use App\Http\Controllers\Web\ShipmentController;
use App\Http\Controllers\Web\TrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - CargoPharma (Global Cold-Chain Visibility Platform)
|--------------------------------------------------------------------------
*/

// Public Authentication
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Application Routes
Route::middleware('auth')->group(function () {
    // Mission Control Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shipments
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
    Route::patch('/shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.status.update');

    // Tracking Events
    Route::post('/shipments/{shipment}/tracking', [TrackingController::class, 'store'])->name('tracking.store');

    // Exception Management
    Route::post('/shipments/{shipment}/exceptions', [ExceptionController::class, 'store'])->name('exceptions.store');
    Route::patch('/exceptions/{exception}/resolve', [ExceptionController::class, 'resolve'])->name('exceptions.resolve');

    // Audit Logs (Admin only)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});
