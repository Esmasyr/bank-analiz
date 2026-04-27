<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Analiz & CSV
    Route::get('/analytics', [AnalyticsController::class, 'analytics'])->name('analytics');
    Route::get('/transactions/csv-import', [AnalyticsController::class, 'csvImportPage'])->name('transactions.csvImport.page');
    Route::post('/transactions/csv-import', [AnalyticsController::class, 'csvImport'])->name('transactions.csvImport');

    // İşlemler
    Route::resource('transactions', TransactionController::class);

    // Chatbot
    Route::post('/chat/ask', [ChatController::class, 'ask'])->name('chat.ask');

});

require __DIR__.'/auth.php';