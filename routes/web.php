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
        'plans' => \App\Models\Plan::where('is_active', true)->orderBy('price_usd')->get(),
    ]);
})->name('home');

use App\Http\Controllers\UserLicenseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\AdminLicenseController;

Route::get('/dashboard', [UserLicenseController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/subscription', [UserLicenseController::class, 'subscription'])
    ->middleware(['auth', 'verified'])->name('subscription');

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
use App\Http\Controllers\Admin\AdminPlanController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\Admin\AdminReleaseController;

// Public Static Pages
Route::get('/p/{slug}', [PageController::class, 'show'])->name('pages.show');

// Public Release Routes
Route::get('/install', [ReleaseController::class, 'install'])->name('releases.install');
Route::get('/install.sh', [ReleaseController::class, 'install']);
Route::get('/uninstall', [ReleaseController::class, 'uninstall'])->name('releases.uninstall');
Route::get('/uninstall.sh', [ReleaseController::class, 'uninstall']);
Route::get('/nimbus.zip', [ReleaseController::class, 'download'])->name('releases.download');

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

    // Plans Management
    Route::get('/plans', [AdminPlanController::class, 'index'])->name('plans.index');
    Route::get('/plans/{plan}/edit', [AdminPlanController::class, 'edit'])->name('plans.edit');
    Route::put('/plans/{plan}', [AdminPlanController::class, 'update'])->name('plans.update');

    // Releases Management
    Route::get('/releases', [AdminReleaseController::class, 'index'])->name('releases.index');
    Route::post('/releases/upload', [AdminReleaseController::class, 'upload'])->name('releases.upload');
    Route::delete('/releases', [AdminReleaseController::class, 'destroy'])->name('releases.destroy');
});

require __DIR__.'/auth.php';
