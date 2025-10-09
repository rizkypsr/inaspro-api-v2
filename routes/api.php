<?php

use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\ProductStockLogController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\GlobalVoucherController;
use App\Http\Controllers\Api\ProductVoucherController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\ShippingRateController;
use App\Http\Controllers\Api\CommunityController;

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('register', [RegisterController::class, 'register'])->name('api.auth.register');
    Route::post('login', [LoginController::class, 'login'])->name('api.auth.login');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('api.auth.forgot-password');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('api.auth.reset-password');
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('api.auth.logout');
    Route::get('me', [LoginController::class, 'me'])->name('api.auth.me');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('product-variants', ProductVariantController::class);
    Route::apiResource('product-stock-logs', ProductStockLogController::class);
    
    // Cart routes
    Route::apiResource('carts', CartController::class);
    
    // Cart items routes for authenticated user
    Route::get('cart/items', [CartController::class, 'getItems'])->name('cart.items.index');
    Route::post('cart/items', [CartController::class, 'addItem'])->name('cart.items.store');
    Route::put('cart/items/{item}', [CartController::class, 'updateItem'])->name('cart.items.update');
    Route::delete('cart/items/{item}', [CartController::class, 'removeItem'])->name('cart.items.destroy');
    Route::delete('cart/items', [CartController::class, 'clearItems'])->name('cart.items.clear');
    
    // Order routes
    Route::apiResource('orders', OrderController::class);
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::put('orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('orders.update-payment-status');
    
    // Global Voucher routes
    Route::apiResource('global-vouchers', GlobalVoucherController::class);
    Route::post('global-vouchers/validate', [GlobalVoucherController::class, 'validate'])->name('global-vouchers.validate');
    Route::get('global-vouchers/{globalVoucher}/usage', [GlobalVoucherController::class, 'usage'])->name('global-vouchers.usage');
    
    // Product Voucher routes
    Route::apiResource('product-vouchers', ProductVoucherController::class);
    Route::post('product-vouchers/validate', [ProductVoucherController::class, 'validate'])->name('product-vouchers.validate');
    Route::get('product-vouchers/{productVoucher}/usage', [ProductVoucherController::class, 'usage'])->name('product-vouchers.usage');
    Route::get('products/{product}/vouchers', [ProductVoucherController::class, 'byProduct'])->name('products.vouchers');
    
    // Province routes
    Route::apiResource('provinces', ProvinceController::class);
    
    // Shipping Rate routes
    Route::apiResource('shipping-rates', ShippingRateController::class);
    
    // Community routes
    Route::apiResource('communities', CommunityController::class);
    
    // Community-specific routes
    Route::prefix('communities/{community}')->group(function () {
        Route::post('join', [CommunityController::class, 'join'])->name('communities.join');
        Route::delete('leave', [CommunityController::class, 'leave'])->name('communities.leave');
        Route::get('members', [CommunityController::class, 'members'])->name('communities.members');
        Route::put('members/{member}/approve', [CommunityController::class, 'approveMember'])->name('communities.members.approve');
        Route::post('posts', [CommunityController::class, 'createPost'])->name('communities.posts.store');
        Route::get('posts', [CommunityController::class, 'posts'])->name('communities.posts.index');
    });
});
