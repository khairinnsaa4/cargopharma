<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiShipmentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CargoPharma REST API Routes (UAS Web Programming 2 - Slide 12 & 13)
|--------------------------------------------------------------------------
*/

// Public Authentication Endpoints
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);

// Protected API Endpoints (Requires Sanctum Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Identity
    Route::get('/user', [ApiAuthController::class, 'user']);
    Route::post('/logout', [ApiAuthController::class, 'logout']);

    // Shipments CRUD
    Route::get('/shipments', [ApiShipmentController::class, 'index']);
    Route::post('/shipments', [ApiShipmentController::class, 'store']);
    Route::get('/shipments/{id}', [ApiShipmentController::class, 'show']);
    Route::put('/shipments/{id}', [ApiShipmentController::class, 'update']);
    Route::patch('/shipments/{id}', [ApiShipmentController::class, 'update']);
    Route::delete('/shipments/{id}', [ApiShipmentController::class, 'destroy']);

    // Tracking Events
    Route::get('/shipments/{id}/tracking', [ApiShipmentController::class, 'getTracking']);
    Route::post('/shipments/{id}/tracking', [ApiShipmentController::class, 'postTracking']);

    // Exceptions
    Route::get('/shipments/{id}/exceptions', [ApiShipmentController::class, 'getExceptions']);
    Route::post('/shipments/{id}/exceptions', [ApiShipmentController::class, 'postException']);
    Route::patch('/exceptions/{id}', [ApiShipmentController::class, 'patchException']);

    // Carriers & Locations
    Route::get('/carriers', [ApiShipmentController::class, 'getCarriers']);
    Route::post('/carriers', [ApiShipmentController::class, 'postCarrier']);
    Route::get('/locations', [ApiShipmentController::class, 'getLocations']);
    Route::post('/locations', [ApiShipmentController::class, 'postLocation']);
});
