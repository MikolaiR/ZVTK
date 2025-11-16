<?php

use App\Http\Controllers\Admin\AutoBrandController;
use App\Http\Controllers\Admin\AutoController as AdminAutoController;
use App\Http\Controllers\Admin\AutoModelController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\ParkingController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\BrandAndModelController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Client\AutoController;
use App\Http\Controllers\Client\AutoTransitionController;
use App\Http\Controllers\Client\ProfileController;
use App\Services\Client\HomeService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

    // Admin Autos
    Route::get('/autos', [AdminAutoController::class, 'index'])->name('autos.index');
    // Create is handled by client page per requirements: link to /autos/create
    Route::get('/autos/{auto}/edit', [AdminAutoController::class, 'edit'])->name('autos.edit');
    Route::put('/autos/{auto}', [AdminAutoController::class, 'update'])->name('autos.update');
    Route::delete('/autos/{auto}', [AdminAutoController::class, 'destroy'])->name('autos.destroy');

    // Auto Location Periods
    Route::post('/autos/{auto}/periods', [AdminAutoController::class, 'storePeriod'])->name('autos.periods.store');
    Route::put('/autos/{auto}/periods/{period}', [AdminAutoController::class, 'updatePeriod'])->name('autos.periods.update');
    Route::post('/autos/{auto}/periods/{period}/close', [AdminAutoController::class, 'closePeriod'])->name('autos.periods.close');
    Route::delete('/autos/{auto}/periods/{period}', [AdminAutoController::class, 'destroyPeriod'])->name('autos.periods.destroy');

    // Roles
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // Permissions
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
    Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

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

    // Parkings
    Route::get('/parkings', [ParkingController::class, 'index'])->name('parkings.index');
    Route::get('/parkings/create', [ParkingController::class, 'create'])->name('parkings.create');
    Route::post('/parkings', [ParkingController::class, 'store'])->name('parkings.store');
    Route::get('/parkings/{parking}/edit', [ParkingController::class, 'edit'])->name('parkings.edit');
    Route::put('/parkings/{parking}', [ParkingController::class, 'update'])->name('parkings.update');
    Route::delete('/parkings/{parking}', [ParkingController::class, 'destroy'])->name('parkings.destroy');

    // Parking Prices
    Route::post('/parkings/{parking}/prices', [ParkingController::class, 'storePrice'])->name('parkings.prices.store');
    Route::put('/parkings/{parking}/prices/{price}', [ParkingController::class, 'updatePrice'])->name('parkings.prices.update');
    Route::delete('/parkings/{parking}/prices/{price}', [ParkingController::class, 'destroyPrice'])->name('parkings.prices.destroy');

    // Customers (Таможни)
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::post('/customers/{id}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
});
