<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

use App\Http\Controllers\UserLicenseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\AdminLicenseController;

Route::get('/dashboard', [UserLicenseController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/licenses/free', [UserLicenseController::class, 'generateFree'])
    ->middleware(['auth', 'verified'])->name('licenses.free');

Route::post('/licenses/{license}/disconnect', [UserLicenseController::class, 'disconnect'])
    ->middleware(['auth', 'verified'])->name('licenses.disconnect');

Route::post('/licenses/{license}/revoke', [UserLicenseController::class, 'revoke'])
    ->middleware(['auth', 'verified'])->name('licenses.revoke');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Payment Routes
    Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment'])->name('payment.initiate');
    Route::post('/payment/verify', [PaymentController::class, 'verifyPayment'])->name('payment.verify');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/licenses', [AdminLicenseController::class, 'index'])->name('licenses.index');
    Route::post('/licenses/generate', [AdminLicenseController::class, 'generate'])->name('licenses.generate');
    Route::patch('/licenses/{license}', [AdminLicenseController::class, 'update'])->name('licenses.update');
    Route::delete('/licenses/{license}', [AdminLicenseController::class, 'destroy'])->name('licenses.destroy');
});

require __DIR__.'/auth.php';
