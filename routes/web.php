<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LogisticsController;
use App\Http\Controllers\OrderController;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

// Admin Panel Routes - Only Admin role can access
Route::middleware(['auth', 'verified', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.marketplace');
    })->name('index');
    
    Route::get('/marketplace', function () {
        return Inertia::render('admin/marketplace');
    })->name('marketplace');
    
    // Marketplace sub-routes
    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        // Categories routes
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::patch('/categories/{category}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
        Route::delete('/categories/{category}/force', [CategoryController::class, 'forceDelete'])->name('categories.force-delete');
        
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::patch('/products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');
        Route::delete('/products/{product}/force', [ProductController::class, 'forceDelete'])->name('products.force-delete');
        
        Route::get('/logistics', [LogisticsController::class, 'index'])->name('logistics.index');
        Route::get('/logistics/create', [LogisticsController::class, 'create'])->name('logistics.create');
        Route::post('/logistics', [LogisticsController::class, 'store'])->name('logistics.store');
        Route::get('/logistics/{shippingRate}', [LogisticsController::class, 'show'])->name('logistics.show');
        Route::get('/logistics/{shippingRate}/edit', [LogisticsController::class, 'edit'])->name('logistics.edit');
        Route::put('/logistics/{shippingRate}', [LogisticsController::class, 'update'])->name('logistics.update');
        Route::delete('/logistics/{shippingRate}', [LogisticsController::class, 'destroy'])->name('logistics.destroy');
        Route::delete('/logistics', [LogisticsController::class, 'bulkDestroy'])->name('logistics.bulk-destroy');
        
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
        Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::put('/orders/{order}/tracking', [OrderController::class, 'updateTracking'])->name('orders.update-tracking');
        Route::post('/orders/{order}/payment-proof', [OrderController::class, 'uploadPaymentProof'])->name('orders.upload-payment-proof');
        Route::delete('/orders/{order}/payment-proof', [OrderController::class, 'removePaymentProof'])->name('orders.remove-payment-proof');
        Route::put('/orders/bulk-update', [OrderController::class, 'bulkUpdate'])->name('orders.bulk-update');
    });
    
    Route::get('/users', function () {
        return Inertia::render('admin/users');
    })->name('users');
    
    Route::get('/analytics', function () {
        return Inertia::render('admin/analytics');
    })->name('analytics');
    
    Route::get('/settings', function () {
        return Inertia::render('admin/settings');
    })->name('settings');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
