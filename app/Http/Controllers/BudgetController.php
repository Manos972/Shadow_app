<?php

namespace App\Http\Controllers;

use App\Services\BudgetAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function __construct(private BudgetAnalysisService $service) {}

    public function index(): View { return view('budget.index'); }
    public function transactions(): View { return view('budget.transactions'); }
    public function categories(): View { return view('budget.categories'); }
    public function accounts(): View { return view('budget.accounts'); }
    public function reports(): View { return view('budget.reports'); }

    public function chartData(): JsonResponse
    {
        $user = auth()->user();
        return response()->json([
            'monthly' => $this->service->monthlyChartData($user),
            'by_category' => $this->service->categoryChartData($user),
            'trend' => $this->service->trendData($user),
        ]);
    }
}
