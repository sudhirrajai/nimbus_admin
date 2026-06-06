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

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\PageController;

// Public Static Pages
Route::get('/p/{slug}', [PageController::class, 'show'])->name('pages.show');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/licenses', [AdminLicenseController::class, 'index'])->name('licenses.index');
    Route::post('/licenses/generate', [AdminLicenseController::class, 'generate'])->name('licenses.generate');
    Route::patch('/licenses/{license}', [AdminLicenseController::class, 'update'])->name('licenses.update');
    Route::delete('/licenses/{license}', [AdminLicenseController::class, 'destroy'])->name('licenses.destroy');

    // Users Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Settings Management
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');

    // Pages Management
    Route::get('/pages', [AdminPageController::class, 'index'])->name('pages.index');
    Route::get('/pages/{page}/edit', [AdminPageController::class, 'edit'])->name('pages.edit');
    Route::put('/pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
});

require __DIR__.'/auth.php';
