<?php

use App\Http\Controllers\Api\ApiExternalServiceController;
use App\Http\Controllers\Web\AuditLogController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CarrierController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ExceptionController;
use App\Http\Controllers\Web\ExceptionPageController;
use App\Http\Controllers\Web\GlobalRadarController;
use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\ShipmentController;
use App\Http\Controllers\Web\TrackingController;
use App\Http\Controllers\Web\TrackingPageController;
use App\Http\Controllers\Web\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - CargoPharma (Formal Enterprise Logistics Platform)
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

// Cloud / Serverless Database Migration Helper
Route::get('/run-migrate', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);

        // Explicitly update or create default users with guaranteed known password
        User::updateOrCreate(
            ['email' => 'admin@cargopharma.com'],
            [
                'name' => 'Administrator Bio Farma',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'phone' => '+62 811-2233-4455',
                'organization' => 'PT Bio Farma (Persero) - Headquarters',
            ]
        );

        User::updateOrCreate(
            ['email' => 'operator@cargopharma.com'],
            [
                'name' => 'Rian Pratama',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'is_active' => true,
                'phone' => '+62 812-9988-7766',
                'organization' => 'PT Bio Farma - Global Export & Cold Chain Logistics',
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@unicef.org'],
            [
                'name' => 'Dr. Adebayo Johnson',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'is_active' => true,
                'phone' => '+45 35 27 35 27',
                'organization' => 'UNICEF Global Supply Division (Polio Outbreak Response)',
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Database Supabase berhasil dimigrasikan dan password ketiga akun dipastikan "password"!',
        ]);
    } catch (Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
        ], 500);
    }
})->name('run-migrate');

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

    // 4. Global Radar (Mata Uang, Berita Antar Benua, & Peta Cuaca Dunia)
    Route::get('/global-radar', [GlobalRadarController::class, 'index'])->name('global-radar.index');
    Route::get('/peta-cuaca', function () {
        return redirect()->to(route('global-radar.index').'#peta-cuaca-section');
    })->name('peta-cuaca');

    // 4. Exceptions (Dedicated Incident Management Page)
    Route::get('/exceptions', [ExceptionPageController::class, 'index'])->name('exceptions.index');
    Route::post('/shipments/{shipment}/exceptions', [ExceptionController::class, 'store'])->name('exceptions.store');
    Route::patch('/exceptions/{exception}/resolve', [ExceptionController::class, 'resolve'])->name('exceptions.resolve');

    // 5. Carriers & Partners
    Route::get('/carriers', [CarrierController::class, 'index'])->name('carriers.index')->middleware('role:admin,operator');
    Route::post('/carriers', [CarrierController::class, 'store'])->name('carriers.store')->middleware('role:admin');
    Route::patch('/carriers/{carrier}', [CarrierController::class, 'update'])->name('carriers.update')->middleware('role:admin');

    // 6. Transit Hubs & Locations
    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index')->middleware('role:admin,operator');
    Route::post('/locations', [LocationController::class, 'store'])->name('locations.store')->middleware('role:admin');
    Route::patch('/locations/{location}', [LocationController::class, 'update'])->name('locations.update')->middleware('role:admin');

    // 7. User Management (Admin only)
    Route::resource('users', UserController::class)->middleware('role:admin')->except(['create', 'show', 'edit']);

    // 8. Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');

    // 9. Reports & Analytics
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index')->middleware('role:admin,operator');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.exportCsv')->middleware('role:admin,operator');

    // 10. Reports & Governance (Admin only)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index')->middleware('role:admin');

    // 11. Live External Services (AJAX Helpers)
    Route::get('/ajax/geocode', [ApiExternalServiceController::class, 'getGeocode'])->name('ajax.geocode');
    Route::get('/ajax/weather', [ApiExternalServiceController::class, 'getWeather'])->name('ajax.weather');
    Route::get('/ajax/currency-rates', [ApiExternalServiceController::class, 'getCurrencyRates'])->name('ajax.currency');
    Route::get('/ajax/time', [ApiExternalServiceController::class, 'getTime'])->name('ajax.time');
    Route::get('/ajax/time/hubs', [ApiExternalServiceController::class, 'getGlobalTimeHubs'])->name('ajax.time.hubs');
    Route::get('/ajax/seaports', [ApiExternalServiceController::class, 'getSeaports'])->name('ajax.seaports');
    Route::get('/ajax/aviation', [ApiExternalServiceController::class, 'getAviation'])->name('ajax.aviation');
    Route::get('/ajax/route', [ApiExternalServiceController::class, 'getRoute'])->name('ajax.route');
});
