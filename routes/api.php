<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\QuotationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LookupController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('quotations/preview', [QuotationController::class, 'calculatePreview']);
    Route::apiResource('quotations', QuotationController::class);
    Route::post('quotations/upload-drawing', [QuotationController::class, 'uploadDrawing']);
    Route::get('quotations/{id}/pdf', [QuotationController::class, 'generatePdf']);
    Route::get('quotations/{id}/preview', [QuotationController::class, 'previewPdf']);
    Route::get('quotations/{id}/download', [QuotationController::class, 'downloadPdf']);

    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('products', ProductController::class);
    Route::post('products/{product}/bom-preview', [\App\Http\Controllers\Api\BomPreviewController::class, 'preview']);
    
    Route::get('designs', [\App\Http\Controllers\Api\DesignController::class, 'index']);
    Route::get('products/{product}/designs', [\App\Http\Controllers\Api\DesignController::class, 'getByProduct']);
    Route::get('designs/{design}', [\App\Http\Controllers\Api\DesignController::class, 'show']);
    
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('lookups', [LookupController::class, 'index']);

    // Admin only routes
    Route::middleware('role:Admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });
});


