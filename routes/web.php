<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ElectricityController;
use App\Http\Controllers\HistoricalRecordController;
use App\Http\Controllers\MonthlyRecordController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WaterBillController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Monthly Records (CRUD)
Route::resource('monthly-records', MonthlyRecordController::class);

// Electricity Tracker
Route::get('/electricity', [ElectricityController::class, 'index'])->name('electricity.index');
Route::put('/electricity/{monthlyRecord}', [ElectricityController::class, 'updateReading'])->name('electricity.update-reading');

// Water Bill Tracker
Route::get('/water-bills', [WaterBillController::class, 'index'])->name('water.index');
Route::put('/water-bills/{monthlyRecord}', [WaterBillController::class, 'updateBill'])->name('water.update-bill');

// Payment Management
Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

// Historical Records
Route::get('/historical-records', [HistoricalRecordController::class, 'index'])->name('historical.index');
Route::get('/historical-records/create', [HistoricalRecordController::class, 'create'])->name('historical.create');
Route::post('/historical-records', [HistoricalRecordController::class, 'store'])->name('historical.store');

// Reports and Analytics
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');

// Settings
Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
