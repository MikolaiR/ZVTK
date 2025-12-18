<?php

use App\Http\Controllers\Api\BrandAndModelController;
use App\Http\Controllers\Client\AutoController;
use App\Http\Controllers\Client\AutoTransitionController;
use App\Http\Controllers\Client\ProfileController;
use App\Services\Client\HomeService;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeService::class, 'index'])->name('home');

    Route::get('/autos', [AutoController::class, 'index'])->name('autos.index');

    // Create Auto (manager/admin)
    Route::middleware('permission:create_auto')->group(function () {
        Route::get('/autos/create', [AutoController::class, 'create'])->name('autos.create');
        Route::post('/autos', [AutoController::class, 'store'])->name('autos.store');

        Route::get('/api/brands/{brand}/models', [BrandAndModelController::class, 'getModels'])->name('api.brand.models');
    });

    Route::get('/autos/{auto}', [AutoController::class, 'show'])->name('autos.show');
    Route::post('/autos/{auto}/transitions', [AutoTransitionController::class, 'store'])->name('autos.transitions');
    Route::get('/autos/{auto}/storage-cost', [AutoTransitionController::class, 'storageCost'])->name('autos.storage-cost');

    Route::get('/profile', ProfileController::class)->name('profile.show');
});