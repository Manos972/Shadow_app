<?php

namespace App\Http\Controllers;

use App\Services\BudgetAnalysisService;
use App\Services\PortfolioAnalysisService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private BudgetAnalysisService $budgetService,
        private PortfolioAnalysisService $portfolioService,
    ) {}

    public function index(): View
    {
        $user = auth()->user();

        $stats = [
            'net_worth' => $user->totalNetWorth() + $user->totalPortfolioValue(),
            'monthly_income' => $this->budgetService->monthlyIncome($user),
            'monthly_expenses' => $this->budgetService->monthlyExpenses($user),
            'portfolio_value' => $user->totalPortfolioValue(),
            'portfolio_pnl' => $this->portfolioService->totalUnrealizedPnl($user),
            'portfolio_day_change' => $this->portfolioService->totalDayChange($user),
        ];

        return view('dashboard', compact('stats'));
    }
}
