<?php

use App\Http\Controllers\Admin\AutoBrandController;
use App\Http\Controllers\Admin\AutoModelController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\BrandAndModelController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Client\AutoController;
use App\Http\Controllers\Client\ProfileController;
use App\Services\Client\HomeServise;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;




Route::middleware('auth')->group(function () {
    Route::get('/', [HomeServise::class, 'index'])->name('home');

    Route::get('/autos', [AutoController::class, 'index'])->name('autos.index');

    // Create Auto (manager/admin)
    Route::middleware('permission:create_auto')->group(function () {
        Route::get('/autos/create', [AutoController::class, 'create'])->name('autos.create');
        Route::post('/autos', [AutoController::class, 'store'])->name('autos.store');

        Route::get('/api/brands/{brand}/models', [BrandAndModelController::class, 'getModels'])->name('api.brand.models');
    });

    Route::get('/profile', ProfileController::class)->name('profile.show');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Admin/Home');
    })->name('home');

    // Users & Roles
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::post('/users/{user}/roles', [UserController::class, 'syncRoles'])->name('users.roles.sync');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');

    // Auto Brands
    Route::get('/auto-brands', [AutoBrandController::class, 'index'])->name('auto.brands.index');
    Route::get('/auto-brands/create', [AutoBrandController::class, 'create'])->name('auto.brands.create');
    Route::post('/auto-brands', [AutoBrandController::class, 'store'])->name('auto.brands.store');
    Route::get('/auto-brands/{brand}/edit', [AutoBrandController::class, 'edit'])->name('auto.brands.edit');
    Route::put('/auto-brands/{brand}', [AutoBrandController::class, 'update'])->name('auto.brands.update');
    Route::delete('/auto-brands/{brand}', [AutoBrandController::class, 'destroy'])->name('auto.brands.destroy');
    Route::post('/auto-brands/{id}/restore', [AutoBrandController::class, 'restore'])->name('auto.brands.restore');

    // Auto Models
    Route::get('/auto-models', [AutoModelController::class, 'index'])->name('auto.models.index');
    Route::get('/auto-models/create', [AutoModelController::class, 'create'])->name('auto.models.create');
    Route::post('/auto-models', [AutoModelController::class, 'store'])->name('auto.models.store');
    Route::get('/auto-models/{model}/edit', [AutoModelController::class, 'edit'])->name('auto.models.edit');
    Route::put('/auto-models/{model}', [AutoModelController::class, 'update'])->name('auto.models.update');
    Route::delete('/auto-models/{model}', [AutoModelController::class, 'destroy'])->name('auto.models.destroy');
    Route::post('/auto-models/{id}/restore', [AutoModelController::class, 'restore'])->name('auto.models.restore');

    // Companies
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    Route::post('/companies/{id}/restore', [CompanyController::class, 'restore'])->name('companies.restore');

    // Colors
    Route::get('/colors', [ColorController::class, 'index'])->name('colors.index');
    Route::get('/colors/create', [ColorController::class, 'create'])->name('colors.create');
    Route::post('/colors', [ColorController::class, 'store'])->name('colors.store');
    Route::get('/colors/{color}/edit', [ColorController::class, 'edit'])->name('colors.edit');
    Route::put('/colors/{color}', [ColorController::class, 'update'])->name('colors.update');
    Route::delete('/colors/{color}', [ColorController::class, 'destroy'])->name('colors.destroy');
    Route::post('/colors/{id}/restore', [ColorController::class, 'restore'])->name('colors.restore');

    // Providers
    Route::get('/providers', [ProviderController::class, 'index'])->name('providers.index');
    Route::get('/providers/create', [ProviderController::class, 'create'])->name('providers.create');
    Route::post('/providers', [ProviderController::class, 'store'])->name('providers.store');
    Route::get('/providers/{provider}/edit', [ProviderController::class, 'edit'])->name('providers.edit');
    Route::put('/providers/{provider}', [ProviderController::class, 'update'])->name('providers.update');
    Route::delete('/providers/{provider}', [ProviderController::class, 'destroy'])->name('providers.destroy');
    Route::post('/providers/{id}/restore', [ProviderController::class, 'restore'])->name('providers.restore');
});
