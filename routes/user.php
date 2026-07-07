<?php

use App\Http\Controllers\Api\BrandAndModelController;
use App\Http\Controllers\Client\AutoController;
use App\Http\Controllers\Client\AutoTransitionController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::view('/instructions', 'client.instructions')->name('instructions.index');

    Route::get('/autos', [AutoController::class, 'index'])->name('autos.index');
    Route::get('/autos/export', [AutoController::class, 'export'])->name('autos.export');

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