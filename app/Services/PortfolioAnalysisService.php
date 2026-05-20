<?php

namespace App\Services;

use App\Models\User;
use App\Models\Position;

class PortfolioAnalysisService
{
    public function totalUnrealizedPnl(User $user): float
    {
        return $user->positions()->where('is_open', true)->get()->sum(fn($p) => $p->unrealizedPnl());
    }

    public function totalDayChange(User $user): float
    {
        return $user->positions()->where('is_open', true)->get()->sum(fn($p) => $p->dayChange());
    }

    public function allocationData(User $user): array
    {
        $positions = $user->positions()->where('is_open', true)->get();
        $total = $positions->sum(fn($p) => $p->currentValue());

        if ($total == 0) return [];

        return $positions->map(fn($p) => [
            'symbol' => $p->symbol,
            'name' => $p->name,
            'value' => $p->currentValue(),
            'percent' => round(($p->currentValue() / $total) * 100, 2),
        ])->sortByDesc('value')->values()->toArray();
    }

    public function performanceChartData(User $user): array
    {
        $portfolios = $user->portfolios()->with(['positions.trades'])->get();
        $result = [];

        foreach ($portfolios as $portfolio) {
            $result[] = [
                'portfolio' => $portfolio->name,
                'color' => $portfolio->color,
                'total_value' => $portfolio->totalValue(),
                'total_cost' => $portfolio->totalCost(),
                'unrealized_pnl' => $portfolio->unrealizedPnl(),
                'realized_pnl' => $portfolio->realizedPnl(),
                'total_pnl' => $portfolio->unrealizedPnl() + $portfolio->realizedPnl(),
            ];
        }

        return $result;
    }

    public function topPerformers(User $user, int $limit = 5): array
    {
        return $user->positions()->where('is_open', true)->get()
            ->sortByDesc(fn($p) => $p->unrealizedPnlPercent())
            ->take($limit)
            ->values()
            ->map(fn($p) => [
                'symbol' => $p->symbol,
                'name' => $p->name,
                'pnl' => $p->unrealizedPnl(),
                'pnl_percent' => $p->unrealizedPnlPercent(),
                'current_price' => $p->current_price,
            ])
            ->toArray();
    }

    public function worstPerformers(User $user, int $limit = 5): array
    {
        return $user->positions()->where('is_open', true)->get()
            ->sortBy(fn($p) => $p->unrealizedPnlPercent())
            ->take($limit)
            ->values()
            ->map(fn($p) => [
                'symbol' => $p->symbol,
                'name' => $p->name,
                'pnl' => $p->unrealizedPnl(),
                'pnl_percent' => $p->unrealizedPnlPercent(),
                'current_price' => $p->current_price,
            ])
            ->toArray();
    }
}
