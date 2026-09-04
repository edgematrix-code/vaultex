<?php

use App\Http\Controllers\Auth\RecoveryPhraseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::post('login/recovery-phrase', [RecoveryPhraseController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('recovery-phrase.login');
});

Route::middleware('auth')->group(function () {
    Route::get('register/recovery-phrase', [RecoveryPhraseController::class, 'show'])
        ->name('recovery-phrase.reveal');
    Route::post('register/recovery-phrase/acknowledge', [RecoveryPhraseController::class, 'acknowledge'])
        ->name('recovery-phrase.ack');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'overview'])->name('overview');
        Route::get('deposit', [WalletController::class, 'deposit'])->name('deposit');
        Route::get('withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
        Route::post('withdraw', [WalletController::class, 'store'])->name('withdraw.store');
        Route::get('receive', [WalletController::class, 'receive'])->name('receive');
    });

    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('{transaction}', [TransactionController::class, 'show'])->name('show');
    });
});

require __DIR__.'/settings.php';
