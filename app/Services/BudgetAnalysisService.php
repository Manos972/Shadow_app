<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

class BudgetAnalysisService
{
    public function monthlyIncome(User $user, ?Carbon $month = null): float
    {
        $month = $month ?? now();
        return $user->transactions()
            ->where('type', 'income')
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->sum('amount');
    }

    public function monthlyExpenses(User $user, ?Carbon $month = null): float
    {
        $month = $month ?? now();
        return $user->transactions()
            ->where('type', 'expense')
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->sum('amount');
    }

    public function monthlySavings(User $user, ?Carbon $month = null): float
    {
        return $this->monthlyIncome($user, $month) - $this->monthlyExpenses($user, $month);
    }

    public function categoryChartData(User $user, ?Carbon $month = null): array
    {
        $month = $month ?? now();
        return $user->transactions()
            ->with('category')
            ->where('type', 'expense')
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->get()
            ->groupBy('category_id')
            ->map(fn($txns) => [
                'name' => $txns->first()->category?->name ?? 'Non catégorisé',
                'color' => $txns->first()->category?->color ?? '#64748b',
                'amount' => $txns->sum('amount'),
            ])
            ->values()
            ->sortByDesc('amount')
            ->values()
            ->toArray();
    }

    public function monthlyChartData(User $user, int $months = 6): array
    {
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $result[] = [
                'month' => $month->format('M Y'),
                'income' => $this->monthlyIncome($user, $month),
                'expenses' => $this->monthlyExpenses($user, $month),
                'savings' => $this->monthlySavings($user, $month),
            ];
        }
        return $result;
    }

    public function trendData(User $user): array
    {
        $transactions = $user->transactions()
            ->where('date', '>=', now()->subMonths(12))
            ->orderBy('date')
            ->get();

        $runningBalance = $user->accounts()->sum('balance');
        $trend = [];

        foreach ($transactions->groupBy(fn($t) => $t->date->format('Y-m')) as $month => $txns) {
            $income = $txns->where('type', 'income')->sum('amount');
            $expenses = $txns->where('type', 'expense')->sum('amount');
            $trend[] = [
                'month' => $month,
                'balance' => $runningBalance,
                'income' => $income,
                'expenses' => $expenses,
            ];
        }

        return $trend;
    }

    public function budgetAdherence(User $user, ?Carbon $month = null): array
    {
        $month = $month ?? now();
        $categories = $user->categories()
            ->where('type', 'expense')
            ->whereNotNull('monthly_budget')
            ->where('is_active', true)
            ->get();

        return $categories->map(function ($cat) use ($month) {
            $spent = $cat->monthlySpent($month->format('Y-m'));
            $budget = $cat->monthly_budget;
            return [
                'category' => $cat->name,
                'icon' => $cat->icon,
                'color' => $cat->color,
                'budget' => $budget,
                'spent' => $spent,
                'remaining' => $budget - $spent,
                'percent' => $budget > 0 ? min(round(($spent / $budget) * 100, 1), 100) : 0,
                'over_budget' => $spent > $budget,
            ];
        })->sortByDesc('percent')->values()->toArray();
    }
}
