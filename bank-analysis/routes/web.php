<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController;

// Ana sayfa → Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// İşlemler CRUD
Route::resource('transactions', TransactionController::class);

// CSV yükleme
Route::get('transactions/import/form', [TransactionController::class, 'importForm'])->name('transactions.import.form');
Route::post('transactions/import/csv', [TransactionController::class, 'importCsv'])->name('transactions.import.csv');

// API Simülasyon
Route::post('transactions/api/simulate', [TransactionController::class, 'apiSimulate'])->name('transactions.api.simulate');
