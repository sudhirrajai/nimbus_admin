<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LicenseController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/v1/verify', [LicenseController::class, 'verify']);
Route::post('/v1/deactivate', [LicenseController::class, 'deactivate']);
Route::post('/v1/heartbeat', [LicenseController::class, 'heartbeat']);
Route::post('/v1/webhooks/razorpay', [\App\Http\Controllers\WebhookController::class, 'handleRazorpay']);

