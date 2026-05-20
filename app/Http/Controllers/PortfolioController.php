<?php

namespace App\Http\Controllers;

use App\Services\PortfolioAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function __construct(private PortfolioAnalysisService $service) {}

    public function index(): View { return view('portfolio.index'); }
    public function positions(): View { return view('portfolio.positions'); }
    public function trades(): View { return view('portfolio.trades'); }
    public function alerts(): View { return view('portfolio.alerts'); }
    public function watchlist(): View { return view('portfolio.watchlist'); }
    public function analysis(): View { return view('portfolio.analysis'); }

    public function performanceData(): JsonResponse
    {
        $user = auth()->user();
        return response()->json($this->service->performanceChartData($user));
    }
}
