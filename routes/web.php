<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', CategoryController::class)->except(['create', 'show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::get('products/{product}/adjust', [ProductController::class, 'adjust'])->name('products.adjust');
    Route::post('products/{product}/adjust', [ProductController::class, 'storeAdjustment'])->name('products.adjust.store');
    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
});
