<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Borrowers\BorrowerController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\LoanProducts\LoanProductController;
use App\Http\Controllers\Payments\MpesaPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/mpesa/stk/callback', [MpesaPaymentController::class, 'callback'])->name('payments.mpesa.callback');

Route::middleware('auth')->group(function (): void {
    Route::get('/', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/dashboard', DashboardController::class)->middleware('permission:dashboard.view');
    Route::resource('borrowers', BorrowerController::class)->only(['index', 'create', 'store'])->middleware('permission:borrowers.manage');
    Route::resource('loan-products', LoanProductController::class)->only(['index', 'create', 'store'])->middleware('permission:loan-products.manage');
    Route::middleware('permission:payments.manage')->group(function (): void {
        Route::get('/payments/mpesa', [MpesaPaymentController::class, 'create'])->name('payments.mpesa.create');
        Route::post('/payments/mpesa', [MpesaPaymentController::class, 'store'])->name('payments.mpesa.store');
    });
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
