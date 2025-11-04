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
use App\Http\Controllers\Api\FantasyEventController;
use App\Http\Controllers\Api\FantasyTeamController;
use App\Http\Controllers\Api\FantasyRegistrationController;
use App\Http\Controllers\Api\FantasyPaymentController;
use App\Http\Controllers\Api\FantasyShoeController;

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
    Route::post('orders/summary', [OrderController::class, 'summary'])->name('orders.summary');
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::put('orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('orders.update-payment-status');
    Route::post('orders/{order}/payment-proof', [OrderController::class, 'uploadPaymentProof'])->name('orders.upload-payment-proof');
    
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
    
    // Fantasy Event routes
    Route::apiResource('fantasy-events', FantasyEventController::class);
    Route::get('fantasy-events/{fantasyEvent}/teams', [FantasyEventController::class, 'teams'])->name('fantasy-events.teams');
    Route::get('fantasy-events/{fantasyEvent}/shoes', [FantasyEventController::class, 'shoes'])->name('fantasy-events.shoes');
    
    // Fantasy Team routes
    Route::prefix('fantasy-events/{fantasyEvent}')->group(function () {
        Route::get('teams', [FantasyTeamController::class, 'index'])->name('fantasy-events.teams.index');
        Route::get('teams/{fantasyEventTeam}', [FantasyTeamController::class, 'show'])->name('fantasy-events.teams.show');
        Route::get('teams-availability', [FantasyTeamController::class, 'availability'])->name('fantasy-events.teams.availability');
        Route::get('teams/{fantasyEventTeam}/members', [FantasyTeamController::class, 'members'])->name('fantasy-events.teams.members');
        Route::get('teams/{fantasyEventTeam}/tshirt-options', [FantasyTeamController::class, 'tshirtOptions'])->name('fantasy-events.teams.tshirt-options');
        
        // Fantasy Shoe routes
        Route::get('shoes', [FantasyShoeController::class, 'index'])->name('fantasy-events.shoes.index');
        Route::get('shoes/{fantasyShoe}', [FantasyShoeController::class, 'show'])->name('fantasy-events.shoes.show');
        Route::get('shoes/{fantasyShoe}/sizes', [FantasyShoeController::class, 'sizes'])->name('fantasy-events.shoes.sizes');
        Route::get('shoes-availability', [FantasyShoeController::class, 'availability'])->name('fantasy-events.shoes.availability');
        Route::post('shoes/check-availability', [FantasyShoeController::class, 'checkAvailability'])->name('fantasy-events.shoes.check-availability');
        Route::get('shoes/popular-sizes', [FantasyShoeController::class, 'popularSizes'])->name('fantasy-events.shoes.popular-sizes');
    });
    
    // Fantasy Registration routes
    Route::apiResource('fantasy-registrations', FantasyRegistrationController::class)->except(['update']);
    Route::put('fantasy-registrations/{fantasyRegistration}/cancel', [FantasyRegistrationController::class, 'cancel'])->name('fantasy-registrations.cancel');
    Route::get('fantasy-events/{fantasyEvent}/registration-summary', [FantasyRegistrationController::class, 'summary'])->name('fantasy-events.registration-summary');
    
    // User specific fantasy registration routes
    Route::get('user/fantasy-registrations/history', [FantasyRegistrationController::class, 'userHistory'])->name('user.fantasy-registrations.history');
    Route::get('user/fantasy-registrations/{id}/detail', [FantasyRegistrationController::class, 'userRegistrationDetail'])->name('user.fantasy-registrations.detail');
    
    // Fantasy Payment routes
    Route::apiResource('fantasy-payments', FantasyPaymentController::class)->except(['update', 'destroy']);
    Route::put('fantasy-payments/{fantasyPayment}/proof', [FantasyPaymentController::class, 'updateProof'])->name('fantasy-payments.update-proof');
    Route::get('fantasy-payments/methods', [FantasyPaymentController::class, 'paymentMethods'])->name('fantasy-payments.methods');
    Route::get('fantasy-payments/statistics', [FantasyPaymentController::class, 'statistics'])->name('fantasy-payments.statistics');
    
    // TV Category routes (GET only)
    Route::get('tv-categories', [App\Http\Controllers\Api\TvCategoryController::class, 'index'])->name('tv-categories.index');
    // Route::get('tv-categories/active', [App\Http\Controllers\Api\TvCategoryController::class, 'active'])->name('tv-categories.active');
    // Route::get('tv-categories/{tvCategory}', [App\Http\Controllers\Api\TvCategoryController::class, 'show'])->name('tv-categories.show');
    
    // TV routes (GET only)
    Route::get('tvs', [App\Http\Controllers\Api\TvController::class, 'index'])->name('tvs.index');
    // Route::get('tvs/categories', [App\Http\Controllers\Api\TvController::class, 'categories'])->name('tvs.categories');
    // Route::get('tvs/{tv}', [App\Http\Controllers\Api\TvController::class, 'show'])->name('tvs.show');
    // Route::get('tvs/category/{category}', [App\Http\Controllers\Api\TvController::class, 'byCategory'])->name('tvs.by-category');
});
