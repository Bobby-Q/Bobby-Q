<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Payments\MpesaPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/mpesa/stk/callback', [MpesaPaymentController::class, 'callback'])->name('payments.mpesa.callback');

Route::middleware('auth')->group(function (): void {
    Route::view('/', 'dashboard')->name('dashboard');
    Route::view('/dashboard', 'dashboard');
    Route::get('/payments/mpesa', [MpesaPaymentController::class, 'create'])->name('payments.mpesa.create');
    Route::post('/payments/mpesa', [MpesaPaymentController::class, 'store'])->name('payments.mpesa.store');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
