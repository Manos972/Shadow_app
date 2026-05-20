<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Budget
    Route::prefix('budget')->name('budget.')->group(function () {
        Route::get('/', [BudgetController::class, 'index'])->name('index');
        Route::get('/transactions', [BudgetController::class, 'transactions'])->name('transactions');
        Route::get('/categories', [BudgetController::class, 'categories'])->name('categories');
        Route::get('/accounts', [BudgetController::class, 'accounts'])->name('accounts');
        Route::get('/reports', [BudgetController::class, 'reports'])->name('reports');
    });

    // Portfolio
    Route::prefix('portfolio')->name('portfolio.')->group(function () {
        Route::get('/', [PortfolioController::class, 'index'])->name('index');
        Route::get('/positions', [PortfolioController::class, 'positions'])->name('positions');
        Route::get('/trades', [PortfolioController::class, 'trades'])->name('trades');
        Route::get('/alerts', [PortfolioController::class, 'alerts'])->name('alerts');
        Route::get('/watchlist', [PortfolioController::class, 'watchlist'])->name('watchlist');
        Route::get('/analysis', [PortfolioController::class, 'analysis'])->name('analysis');
    });

    // API JSON pour charts
    Route::prefix('api')->group(function () {
        Route::get('/stock/{symbol}', [StockController::class, 'quote'])->name('api.stock.quote');
        Route::get('/stock/{symbol}/history', [StockController::class, 'history'])->name('api.stock.history');
        Route::get('/stock/search/{query}', [StockController::class, 'search'])->name('api.stock.search');
        Route::get('/budget/chart-data', [BudgetController::class, 'chartData'])->name('api.budget.chart');
        Route::get('/portfolio/performance', [PortfolioController::class, 'performanceData'])->name('api.portfolio.performance');
    });
});
