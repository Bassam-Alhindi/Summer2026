<?php

use App\Http\Controllers\AssistantController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('reports', [ReportController::class, 'index'])->name('reports');
    Route::get('assistant', fn () => Inertia::render('AIAssistant'))->name('ai-assistant');
    // البث يستهلك عاملاً كاملاً + رصيد API، فنحدّه بشكل أضيق من بقية الكتابات
    Route::post('assistant/stream', [AssistantController::class, 'stream'])
        ->middleware('throttle:12,1')
        ->name('assistant.stream');

    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::put('transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
        Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    });

    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });
});

require __DIR__.'/settings.php';
