<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AutoBrandController;
use App\Http\Controllers\Admin\AutoModelController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

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
});
