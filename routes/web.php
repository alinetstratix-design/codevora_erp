<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardWebController;
use App\Http\Controllers\Web\CustomerWebController;
use App\Http\Controllers\Web\ProductWebController;
use App\Http\Controllers\Web\CompanySettingController;
use App\Http\Controllers\Web\QuotationWebController;
use App\Http\Controllers\Web\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardWebController::class, 'index'])->name('dashboard');

    Route::post('customers/ajax', [CustomerWebController::class, 'storeAjax'])->name('customers.storeAjax');
    Route::resource('customers', CustomerWebController::class);
    
    Route::resource('products', ProductWebController::class);
    Route::get('products/{product}/designs', [\App\Http\Controllers\Api\DesignController::class, 'getByProduct'])->name('products.designs');

    Route::get('/company-settings', [CompanySettingController::class, 'edit'])->name('company.edit');
    Route::put('/company-settings', [CompanySettingController::class, 'update'])->name('company.update');
    
    // Master Data Routes
    Route::resource('materials', \App\Http\Controllers\Web\MaterialWebController::class);

    // Quotation Management & Validation Workflow Routes
    Route::post('/quotations/calculate', [QuotationWebController::class, 'calculate'])
        ->middleware('throttle:60,1')
        ->name('quotations.calculate');
        
    Route::get('/quotations/{quotation}/preview-pdf', [QuotationWebController::class, 'previewPdf'])->name('quotations.previewPdf');
    Route::get('/quotations/{quotation}/download-pdf', [QuotationWebController::class, 'downloadPdf'])->name('quotations.downloadPdf');
    Route::get('/quotations/{quotation}/bom', [QuotationWebController::class, 'showBom'])->name('quotations.showBom');
    Route::get('/quotations/{quotation}/validate', [QuotationWebController::class, 'validateAjax'])->name('quotations.validateAjax');
    Route::post('/quotations/{quotation}/approve', [QuotationWebController::class, 'approve'])->name('quotations.approve');
    
    // Explicitly apply 30,1 throttle just to the store/update methods, wrapping the resource
    Route::resource('quotations', QuotationWebController::class)
        ->only(['store', 'update'])
        ->middleware('throttle:30,1');
        
    Route::resource('quotations', QuotationWebController::class)
        ->except(['store', 'update']);
});
