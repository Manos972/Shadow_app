<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\StockDataService::class);
        $this->app->singleton(\App\Services\BudgetAnalysisService::class);
        $this->app->singleton(\App\Services\PortfolioAnalysisService::class);
    }

    public function boot(): void
    {
        //
    }
}
