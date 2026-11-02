<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AuditLogController;
use App\Http\Controllers\Web\CarrierController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ExceptionController;
use App\Http\Controllers\Web\ExceptionPageController;
use App\Http\Controllers\Web\ShipmentController;
use App\Http\Controllers\Web\TrackingController;
use App\Http\Controllers\Web\TrackingPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - CargoTrace (Formal Enterprise Logistics Platform)
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
    // 1. Dashboard (Overview)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Shipments
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
    Route::patch('/shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.status.update');

    // 3. Tracking (Dedicated Map & Status Page)
    Route::get('/tracking', [TrackingPageController::class, 'index'])->name('tracking.index');
    Route::post('/shipments/{shipment}/tracking', [TrackingController::class, 'store'])->name('tracking.store');

    // 4. Exceptions (Dedicated Incident Management Page)
    Route::get('/exceptions', [ExceptionPageController::class, 'index'])->name('exceptions.index');
    Route::post('/shipments/{shipment}/exceptions', [ExceptionController::class, 'store'])->name('exceptions.store');
    Route::patch('/exceptions/{exception}/resolve', [ExceptionController::class, 'resolve'])->name('exceptions.resolve');

    // 5. Carriers & Partners
    Route::get('/carriers', [CarrierController::class, 'index'])->name('carriers.index');
    Route::post('/carriers', [CarrierController::class, 'store'])->name('carriers.store');

    // 6. Reports & Governance (Admin only)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});
