<?php

use App\Http\Controllers\Api\DesignController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CompanySettingController;
use App\Http\Controllers\Web\CustomerWebController;
use App\Http\Controllers\Web\DashboardWebController;
use App\Http\Controllers\Web\MaterialWebController;
use App\Http\Controllers\Web\PlatformController;
use App\Http\Controllers\Web\ProductWebController;
use App\Http\Controllers\Web\QuotationWebController;
use App\Http\Controllers\Web\TeamController;
use Illuminate\Support\Facades\Route;

// Public marketing page. Signed-in users are sent to their dashboard.
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Guests: sign in and password recovery (throttled).
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Shared quotation PDF for clients: no login, only with a valid, unexpired signed link.
Route::get('/share/quotations/{quotation}/pdf', [QuotationWebController::class, 'sharedPdf'])
    ->name('quotations.shared')
    ->middleware('signed');

Route::middleware(['auth', 'active'])->group(function () {
    // Available even when a password change is still pending.
    Route::get('/account/password', [AccountController::class, 'editPassword'])->name('account.password.edit');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');

    Route::middleware('password.changed')->group(function () {
        Route::get('/dashboard', [DashboardWebController::class, 'index'])->name('dashboard');

        // Platform console: Super Admin only.
        Route::middleware('role:SuperAdmin')->prefix('platform')->name('platform.')->group(function () {
            Route::get('/', [PlatformController::class, 'index'])->name('index');
            Route::get('/companies/create', [PlatformController::class, 'create'])->name('companies.create');
            Route::post('/companies', [PlatformController::class, 'store'])->name('companies.store');
            Route::patch('/companies/{company}/toggle', [PlatformController::class, 'toggleActive'])->name('companies.toggle');
            Route::post('/companies/{company}/admins/{user}/reset-password', [PlatformController::class, 'resetAdminPassword'])->name('companies.admins.reset');
        });

        // Company team: company Admin only.
        Route::middleware('role:Admin')->prefix('team')->name('team.')->group(function () {
            Route::get('/', [TeamController::class, 'index'])->name('index');
            Route::get('/create', [TeamController::class, 'create'])->name('create');
            Route::post('/', [TeamController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [TeamController::class, 'edit'])->name('edit');
            Route::put('/{user}', [TeamController::class, 'update'])->name('update');
            Route::post('/{user}/reset-password', [TeamController::class, 'resetPassword'])->name('reset-password');
        });

        Route::post('customers/ajax', [CustomerWebController::class, 'storeAjax'])->name('customers.storeAjax');
        Route::resource('customers', CustomerWebController::class);

        // Product and master data: Admin and Manager.
        Route::middleware('role:Admin,Manager')->group(function () {
            Route::resource('products', ProductWebController::class);
            Route::resource('materials', MaterialWebController::class);

            Route::get('/company-settings', [CompanySettingController::class, 'edit'])->name('company.edit');
            Route::put('/company-settings', [CompanySettingController::class, 'update'])->name('company.update');
        });
        Route::get('products/{product}/designs', [DesignController::class, 'getByProduct'])->name('products.designs');

        // Quotations.
        Route::post('/quotations/calculate', [QuotationWebController::class, 'calculate'])
            ->middleware('throttle:60,1')
            ->name('quotations.calculate');

        Route::get('/quotations/{quotation}/preview-pdf', [QuotationWebController::class, 'previewPdf'])->name('quotations.previewPdf');
        Route::get('/quotations/{quotation}/download-pdf', [QuotationWebController::class, 'downloadPdf'])->name('quotations.downloadPdf');
        Route::get('/quotations/{quotation}/bom', [QuotationWebController::class, 'showBom'])->name('quotations.showBom');
        Route::get('/quotations/{quotation}/validate', [QuotationWebController::class, 'validateAjax'])->name('quotations.validateAjax');
        Route::post('/quotations/{quotation}/approve', [QuotationWebController::class, 'approve'])->name('quotations.approve');
        Route::post('/quotations/{quotation}/status', [QuotationWebController::class, 'updateStatus'])->name('quotations.status');

        Route::resource('quotations', QuotationWebController::class)
            ->only(['store', 'update'])
            ->middleware('throttle:30,1');

        Route::resource('quotations', QuotationWebController::class)
            ->except(['store', 'update']);
    });
});
