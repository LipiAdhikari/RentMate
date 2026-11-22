<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\ElectricityController;
use App\Http\Controllers\HistoricalRecordController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MonthlyRecordController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WaterBillController;
use Illuminate\Support\Facades\Route;

// Guest routes (authentication) - Public access
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'store'])->name('register.store');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Landing page (public)
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Documentation (public)
Route::get('/docs', [DocsController::class, 'index'])->name('docs.index');

// Authenticated routes (require login)
Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Monthly Records (CRUD)
    Route::resource('monthly-records', MonthlyRecordController::class);

    // Flexible Electricity Billing System
    Route::get('/electricity', [ElectricityController::class, 'index'])->name('electricity.index');
    Route::post('/electricity/bills', [ElectricityController::class, 'store'])->name('electricity.bills.store');
    Route::put('/electricity/bills/{electricityBill}', [ElectricityController::class, 'update'])->name('electricity.bills.update');
    Route::delete('/electricity/bills/{electricityBill}', [ElectricityController::class, 'destroy'])->name('electricity.bills.destroy');
    Route::post('/electricity/bills/{electricityBill}/payments', [ElectricityController::class, 'storePayment'])->name('electricity.bills.payments.store');
    Route::delete('/electricity/payments/{payment}', [ElectricityController::class, 'destroyPayment'])->name('electricity.payments.destroy');
    Route::put('/electricity/{monthlyRecord}', [ElectricityController::class, 'updateReading'])->name('electricity.update-reading');

    // Water Bill Tracker
    Route::get('/water-bills', [WaterBillController::class, 'index'])->name('water.index');
    Route::put('/water-bills/{monthlyRecord}', [WaterBillController::class, 'updateBill'])->name('water.update-bill');

    // Payment Management
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
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
});
