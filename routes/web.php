<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $selfHostedPlans = \App\Models\Plan::where('is_active', true)
        ->selfHosted()
        ->orderBy('price_usd')
        ->get();

    $managedHostingPlans = \App\Models\Plan::where('is_active', true)
        ->managedHosting()
        ->orderBy('price_usd')
        ->get();

    $testimonials = \Illuminate\Support\Facades\Schema::hasTable('testimonials')
        ? \App\Models\Testimonial::active()->get()
        : collect();

    return Inertia::render('Landing', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'selfHostedPlans' => $selfHostedPlans,
        'managedHostingPlans' => $managedHostingPlans,
        'plans' => $selfHostedPlans,
        'testimonials' => $testimonials,
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
use App\Http\Controllers\Admin\AdminInvoiceController;
use App\Http\Controllers\InvoiceController;
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

// Invoices Routes (Client)
Route::middleware(['auth'])->group(function () {
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/licenses', [AdminLicenseController::class, 'index'])->name('licenses.index');
    Route::post('/licenses/generate', [AdminLicenseController::class, 'generate'])->name('licenses.generate');
    Route::patch('/licenses/{license}', [AdminLicenseController::class, 'update'])->name('licenses.update');
    Route::delete('/licenses/{license}', [AdminLicenseController::class, 'destroy'])->name('licenses.destroy');

    // Users Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');
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
    Route::post('/plans', [AdminPlanController::class, 'store'])->name('plans.store');
    Route::get('/plans/{plan}/edit', [AdminPlanController::class, 'edit'])->name('plans.edit');
    Route::put('/plans/{plan}', [AdminPlanController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{plan}', [AdminPlanController::class, 'destroy'])->name('plans.destroy');
    Route::post('/plans/{plan}/toggle-active', [AdminPlanController::class, 'toggleActive'])->name('plans.toggle-active');

    // Releases Management
    Route::get('/releases', [AdminReleaseController::class, 'index'])->name('releases.index');
    Route::post('/releases/upload', [AdminReleaseController::class, 'upload'])->name('releases.upload');
    Route::delete('/releases', [AdminReleaseController::class, 'destroy'])->name('releases.destroy');

    // Bug Reports Management
    Route::get('/reports', [\App\Http\Controllers\Admin\AdminBugReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/{id}/status', [\App\Http\Controllers\Admin\AdminBugReportController::class, 'updateStatus'])->name('reports.update-status');
    Route::delete('/reports/{id}', [\App\Http\Controllers\Admin\AdminBugReportController::class, 'destroy'])->name('reports.destroy');

    // Feature Overrides for Licenses
    Route::post('/licenses/{license}/features', [AdminLicenseController::class, 'updateFeatures'])->name('licenses.features');

    // Managed Hosting Management (Admin)
    Route::get('/hosting', [\App\Http\Controllers\Admin\AdminHostingController::class, 'index'])->name('hosting.index');
    Route::post('/hosting/servers', [\App\Http\Controllers\Admin\AdminHostingController::class, 'storeServer'])->name('hosting.servers.store');
    Route::put('/hosting/servers/{server}', [\App\Http\Controllers\Admin\AdminHostingController::class, 'updateServer'])->name('hosting.servers.update');
    Route::delete('/hosting/servers/{server}', [\App\Http\Controllers\Admin\AdminHostingController::class, 'destroyServer'])->name('hosting.servers.destroy');
    Route::get('/hosting/servers/{server}/sso', [\App\Http\Controllers\Admin\AdminHostingController::class, 'loginServer'])->name('hosting.servers.sso');

    Route::post('/hosting/accounts', [\App\Http\Controllers\Admin\AdminHostingController::class, 'storeAccount'])->name('hosting.accounts.store');
    Route::put('/hosting/accounts/{account}', [\App\Http\Controllers\Admin\AdminHostingController::class, 'updateAccount'])->name('hosting.accounts.update');
    Route::delete('/hosting/accounts/{account}', [\App\Http\Controllers\Admin\AdminHostingController::class, 'destroyAccount'])->name('hosting.accounts.destroy');
    Route::get('/hosting/accounts/{account}/sso', [\App\Http\Controllers\Admin\AdminHostingController::class, 'loginAccount'])->name('hosting.accounts.sso');
    Route::post('/hosting/accounts/{account}/renewal-invoice', [\App\Http\Controllers\Admin\AdminHostingController::class, 'generateRenewalInvoice'])->name('hosting.accounts.renewal-invoice');
    Route::post('/hosting/renewals/check', [\App\Http\Controllers\Admin\AdminHostingController::class, 'runRenewalCheck'])->name('hosting.renewals.check');

    Route::patch('/hosting/requests/{hostingRequest}', [\App\Http\Controllers\Admin\AdminHostingController::class, 'updateRequestStatus'])->name('hosting.requests.update');

    // Invoices Management (Admin)
    Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
    Route::post('/invoices', [AdminInvoiceController::class, 'store'])->name('invoices.store');
    Route::patch('/invoices/{invoice}/status', [AdminInvoiceController::class, 'updateStatus'])->name('invoices.update-status');
    Route::delete('/invoices/{invoice}', [AdminInvoiceController::class, 'destroy'])->name('invoices.destroy');

    // Testimonials Management (Admin)
    Route::get('/testimonials', [\App\Http\Controllers\Admin\AdminTestimonialController::class, 'index'])->name('testimonials.index');
    Route::post('/testimonials', [\App\Http\Controllers\Admin\AdminTestimonialController::class, 'store'])->name('testimonials.store');
    Route::put('/testimonials/{testimonial}', [\App\Http\Controllers\Admin\AdminTestimonialController::class, 'update'])->name('testimonials.update');
    Route::delete('/testimonials/{testimonial}', [\App\Http\Controllers\Admin\AdminTestimonialController::class, 'destroy'])->name('testimonials.destroy');
    Route::post('/testimonials/{testimonial}/toggle-active', [\App\Http\Controllers\Admin\AdminTestimonialController::class, 'toggleActive'])->name('testimonials.toggle-active');
});

// Client Managed Hosting Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/hosting/request', [\App\Http\Controllers\HostingController::class, 'submitRequest'])->name('hosting.request.submit');
    Route::get('/hosting/accounts/{account}/sso', [\App\Http\Controllers\HostingController::class, 'ssoLogin'])->name('hosting.accounts.client-sso');
});

require __DIR__.'/auth.php';
