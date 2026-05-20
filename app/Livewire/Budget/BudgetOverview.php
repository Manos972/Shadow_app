<?php

namespace App\Livewire\Budget;

use App\Services\BudgetAnalysisService;
use Livewire\Component;

class BudgetOverview extends Component
{
    public string $selectedMonth;

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    public function render()
    {
        $user = auth()->user();
        $service = app(BudgetAnalysisService::class);
        $month = \Carbon\Carbon::createFromFormat('Y-m', $this->selectedMonth);

        return view('livewire.budget.budget-overview', [
            'income' => $service->monthlyIncome($user, $month),
            'expenses' => $service->monthlyExpenses($user, $month),
            'savings' => $service->monthlySavings($user, $month),
            'budgetItems' => $service->budgetAdherence($user, $month),
            'categoryData' => $service->categoryChartData($user, $month),
            'monthlyData' => $service->monthlyChartData($user, 6),
        ]);
    }
}
