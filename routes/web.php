<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisteredOwnerController;
use App\Http\Controllers\Auth\VerificationCodeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Owner;
use App\Http\Controllers\Seller;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('register', [RegisteredOwnerController::class, 'create'])->name('register');
    Route::post('register', [RegisteredOwnerController::class, 'store'])
        ->middleware('throttle:login')
        ->name('register.store');
    Route::post('register/code', [VerificationCodeController::class, 'registration'])
        ->middleware('throttle:sms')
        ->name('register.code');

    Route::post('forgot-password/code', [VerificationCodeController::class, 'passwordReset'])
        ->middleware('throttle:sms')
        ->name('password.code');
    Route::post('reset-password', [PasswordResetController::class, 'store'])
        ->middleware('throttle:login')
        ->name('password.update');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/', HomeController::class)->name('home');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function (): void {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::resource('shop-types', Admin\ShopTypeController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('owners', [Admin\OwnerController::class, 'index'])->name('owners.index');
        Route::patch('owners/{owner}/status', [Admin\OwnerController::class, 'toggleStatus'])->name('owners.status');

        Route::get('shops', [Admin\ShopController::class, 'index'])->name('shops.index');
        Route::patch('shops/{shop}/status', [Admin\ShopController::class, 'toggleStatus'])->name('shops.status');

        Route::get('sms', Admin\SmsController::class)->name('sms.index');

        Route::prefix('subscriptions')->group(function (): void {
            Route::resource('plans', Admin\PlanController::class)->only(['index', 'store', 'update']);
        });

        Route::get('subscriptions', [Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::patch('subscriptions/{shop}', [Admin\SubscriptionController::class, 'update'])->name('subscriptions.update');
    });

    Route::prefix('owner')->name('owner.')->middleware('role:owner')->group(function (): void {
        Route::get('/', Owner\DashboardController::class)->name('dashboard');

        Route::resource('shops', Owner\ShopController::class)
            ->except('show')
            ->middlewareFor(['edit', 'update', 'destroy'], ['can:manage,shop', 'subscribed']);

        Route::middleware(['can:manage,shop', 'subscribed'])->scopeBindings()->group(function (): void {
            Route::resource('shops.products', Owner\ProductController::class)->except('show');
            Route::resource('shops.sellers', Owner\SellerController::class)->except('show');

            Route::middleware('feature:product_import')->group(function (): void {
                Route::post('shops/{shop}/products/import', [Owner\ProductImportController::class, 'store'])->name('shops.products.import');
                Route::get('shops/{shop}/products/import-template', [Owner\ProductImportController::class, 'template'])->name('shops.products.import-template');
            });

            Route::middleware('feature:stock_control')->group(function (): void {
                Route::get('shops/{shop}/stock-receipts', [Owner\StockReceiptController::class, 'index'])->name('shops.stock-receipts.index');
                Route::post('shops/{shop}/products/{product}/receipts', [Owner\StockReceiptController::class, 'store'])->name('shops.products.receipts.store');
            });

            Route::resource('shops.customers', Owner\CustomerController::class)
                ->only(['index', 'store', 'update', 'destroy'])
                ->middleware('feature:customers');

            Route::middleware('feature:debts')->group(function (): void {
                Route::resource('shops.debts', Owner\DebtController::class)->only(['index', 'store']);
                Route::post('shops/{shop}/debts/{debt}/payments', [Owner\DebtController::class, 'pay'])->name('shops.debts.pay');
            });
        });

        Route::get('seller-credentials', Owner\SellerCredentialController::class)->name('seller-credentials');

        Route::resource('sales', Owner\SaleController::class)
            ->only(['index', 'show'])
            ->middlewareFor('show', 'can:view,sale');
    });

    Route::prefix('seller')->name('seller.')->middleware(['role:seller', 'subscribed'])->group(function (): void {
        Route::get('/', Seller\DashboardController::class)->name('dashboard');

        Route::resource('sales', Seller\SaleController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->middlewareFor('show', 'can:view,sale');

        Route::resource('customers', Seller\CustomerController::class)
            ->only(['index', 'store'])
            ->middleware('feature:customers');
    });
});
