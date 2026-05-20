<?php
namespace App\Services;

use App\Models\User;
use App\Models\Portfolio;
use App\Models\Dividend;
use App\Models\PortfolioSnapshot;

class PortfolioOptimizationService
{
    private float $riskFreeRate = 0.045; // BOC rate ~4.5%

    public function sharpeRatio(Portfolio $portfolio): ?float
    {
        $snapshots = PortfolioSnapshot::where('portfolio_id', $portfolio->id)
            ->orderBy('snapshot_date')
            ->limit(252)
            ->pluck('total_value')
            ->toArray();

        if (count($snapshots) < 30) return null;

        $returns = [];
        for ($i = 1; $i < count($snapshots); $i++) {
            if ($snapshots[$i-1] > 0) {
                $returns[] = ($snapshots[$i] - $snapshots[$i-1]) / $snapshots[$i-1];
            }
        }

        if (empty($returns)) return null;

        $avgReturn = array_sum($returns) / count($returns);
        $variance = array_sum(array_map(fn($r) => pow($r - $avgReturn, 2), $returns)) / count($returns);
        $stdDev = sqrt($variance);

        if ($stdDev == 0) return null;

        $annualizedReturn = $avgReturn * 252;
        $annualizedStd = $stdDev * sqrt(252);

        return round(($annualizedReturn - $this->riskFreeRate) / $annualizedStd, 3);
    }

    public function annualizedReturn(Portfolio $portfolio): ?float
    {
        $snapshots = PortfolioSnapshot::where('portfolio_id', $portfolio->id)
            ->orderBy('snapshot_date')
            ->get();

        if ($snapshots->count() < 2) return null;

        $first = $snapshots->first();
        $last = $snapshots->last();

        if ($first->total_value <= 0) return null;

        $days = $first->snapshot_date->diffInDays($last->snapshot_date);
        if ($days < 1) return null;

        $totalReturn = ($last->total_value - $first->total_value) / $first->total_value;
        $years = $days / 365.25;

        return round((pow(1 + $totalReturn, 1 / $years) - 1) * 100, 2);
    }

    public function volatility(Portfolio $portfolio): ?float
    {
        $snapshots = PortfolioSnapshot::where('portfolio_id', $portfolio->id)
            ->orderBy('snapshot_date')
            ->pluck('total_value')
            ->toArray();

        if (count($snapshots) < 10) return null;

        $returns = [];
        for ($i = 1; $i < count($snapshots); $i++) {
            if ($snapshots[$i-1] > 0) {
                $returns[] = ($snapshots[$i] - $snapshots[$i-1]) / $snapshots[$i-1];
            }
        }

        if (empty($returns)) return null;
        $avg = array_sum($returns) / count($returns);
        $variance = array_sum(array_map(fn($r) => pow($r - $avg, 2), $returns)) / count($returns);

        return round(sqrt($variance * 252) * 100, 2);
    }

    public function rebalancingPlan(Portfolio $portfolio, array $targetAllocation): array
    {
        $positions = $portfolio->positions()->where('is_open', true)->get();
        $totalValue = $positions->sum(fn($p) => $p->currentValue());

        if ($totalValue <= 0) return [];

        $plan = [];
        foreach ($targetAllocation as $symbol => $targetPercent) {
            $position = $positions->firstWhere('symbol', $symbol);
            $currentValue = $position?->currentValue() ?? 0;
            $currentPercent = $totalValue > 0 ? ($currentValue / $totalValue) * 100 : 0;
            $targetValue = ($targetPercent / 100) * $totalValue;
            $diff = $targetValue - $currentValue;

            $plan[] = [
                'symbol' => $symbol,
                'name' => $position?->name ?? $symbol,
                'current_percent' => round($currentPercent, 2),
                'target_percent' => $targetPercent,
                'current_value' => round($currentValue, 2),
                'target_value' => round($targetValue, 2),
                'action' => $diff > 50 ? 'buy' : ($diff < -50 ? 'sell' : 'hold'),
                'amount' => round(abs($diff), 2),
                'shares_approx' => $position?->current_price && $position->current_price > 0
                    ? round(abs($diff) / $position->current_price, 4)
                    : null,
            ];
        }

        return $plan;
    }

    public function sectorAllocation(Portfolio $portfolio): array
    {
        $sectorMap = [
            'SHOP.TO' => 'Technologie', 'NVDA' => 'Technologie', 'AAPL' => 'Technologie',
            'MSFT' => 'Technologie', 'AMD' => 'Technologie', 'GOOGL' => 'Technologie',
            'RY.TO' => 'Finance', 'TD.TO' => 'Finance', 'BNS.TO' => 'Finance',
            'BMO.TO' => 'Finance', 'CM.TO' => 'Finance', 'NA.TO' => 'Finance',
            'CNR.TO' => 'Industriels', 'CP.TO' => 'Industriels', 'WSP.TO' => 'Industriels',
            'SU.TO' => 'Énergie', 'CVE.TO' => 'Énergie', 'CNQ.TO' => 'Énergie',
            'ABX.TO' => 'Matériaux', 'WPM.TO' => 'Matériaux', 'AGI.TO' => 'Matériaux',
            'ATD.TO' => 'Consommation', 'MRU.TO' => 'Consommation', 'L.TO' => 'Consommation',
            'MFC.TO' => 'Assurance', 'SLF.TO' => 'Assurance', 'GWO.TO' => 'Assurance',
            'XEQT.TO' => 'ETF Monde', 'VFV.TO' => 'ETF S&P500', 'ZSP.TO' => 'ETF S&P500',
            'VCNS.TO' => 'ETF Conservateur', 'VBAL.TO' => 'ETF Balancé',
        ];

        $positions = $portfolio->positions()->where('is_open', true)->get();
        $totalValue = $positions->sum(fn($p) => $p->currentValue());
        $sectors = [];

        foreach ($positions as $pos) {
            $sector = $sectorMap[$pos->symbol] ?? 'Autre';
            $sectors[$sector] = ($sectors[$sector] ?? 0) + $pos->currentValue();
        }

        $result = [];
        foreach ($sectors as $sector => $value) {
            $result[] = [
                'sector' => $sector,
                'value' => round($value, 2),
                'percent' => $totalValue > 0 ? round(($value / $totalValue) * 100, 2) : 0,
            ];
        }

        usort($result, fn($a, $b) => $b['value'] <=> $a['value']);
        return $result;
    }

    public function dividendYield(User $user): float
    {
        $annualDividends = Dividend::where('user_id', $user->id)
            ->where('ex_date', '>=', now()->subYear())
            ->sum(\DB::raw('amount_per_share * shares'));

        $portfolioValue = $user->totalPortfolioValue();

        if ($portfolioValue <= 0) return 0;
        return round(($annualDividends / $portfolioValue) * 100, 2);
    }

    public function totalDividendsYTD(User $user): float
    {
        return Dividend::where('user_id', $user->id)
            ->whereYear('ex_date', now()->year)
            ->sum(\DB::raw('amount_per_share * shares'));
    }

    public function currencyExposure(Portfolio $portfolio): array
    {
        $cadSymbols = ['.TO', '.V', '.CN'];
        $positions = $portfolio->positions()->where('is_open', true)->get();
        $totalValue = $positions->sum(fn($p) => $p->currentValue());

        $cad = 0; $usd = 0; $other = 0;
        foreach ($positions as $pos) {
            $isCAD = collect($cadSymbols)->some(fn($suffix) => str_ends_with($pos->symbol, $suffix));
            if ($isCAD) $cad += $pos->currentValue();
            else $usd += $pos->currentValue();
        }

        return [
            'CAD' => ['value' => round($cad, 2), 'percent' => $totalValue > 0 ? round(($cad / $totalValue) * 100, 1) : 0],
            'USD' => ['value' => round($usd, 2), 'percent' => $totalValue > 0 ? round(($usd / $totalValue) * 100, 1) : 0],
        ];
    }
}
