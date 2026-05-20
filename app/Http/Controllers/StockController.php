<?php

namespace App\Http\Controllers;

use App\Services\StockDataService;
use Illuminate\Http\JsonResponse;

class StockController extends Controller
{
    public function __construct(private StockDataService $stockService) {}

    public function quote(string $symbol): JsonResponse
    {
        try {
            $data = $this->stockService->getQuote(strtoupper($symbol));
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function history(string $symbol): JsonResponse
    {
        try {
            $data = $this->stockService->getHistory(strtoupper($symbol));
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function search(string $query): JsonResponse
    {
        try {
            $results = $this->stockService->search($query);
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
