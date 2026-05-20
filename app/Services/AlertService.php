<?php

namespace App\Services;

use App\Models\StockAlert;
use App\Models\MarketDataCache;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AlertService
{
    public function processAlerts(): int
    {
        $triggered = 0;
        $alerts = StockAlert::with('user')->where('is_active', true)->whereNull('triggered_at')->get();

        foreach ($alerts as $alert) {
            $market = MarketDataCache::where('symbol', $alert->symbol)->first();
            if (!$market) continue;

            $shouldTrigger = match($alert->type) {
                'price_above' => $market->price >= $alert->threshold,
                'price_below' => $market->price <= $alert->threshold,
                'change_percent_above' => $market->changePercent() >= $alert->threshold,
                'change_percent_below' => $market->changePercent() <= -abs($alert->threshold),
                'rsi_above' => $market->rsi_14 && $market->rsi_14 >= $alert->threshold,
                'rsi_below' => $market->rsi_14 && $market->rsi_14 <= $alert->threshold,
                default => false,
            };

            if ($shouldTrigger) {
                $alert->update(['triggered_at' => now(), 'is_active' => false]);
                $triggered++;

                if ($alert->notify_email) {
                    $this->sendEmailAlert($alert, $market->price);
                }
            }
        }

        return $triggered;
    }

    private function sendEmailAlert(StockAlert $alert, float $currentPrice): void
    {
        try {
            Mail::raw(
                "Alerte déclenchée pour {$alert->symbol}\n" .
                "Condition : {$alert->getConditionLabel()}\n" .
                "Prix actuel : {$currentPrice}\n" .
                "Date : " . now()->format('d/m/Y H:i'),
                function ($msg) use ($alert) {
                    $msg->to($alert->user->email)
                        ->subject("⚠️ Alerte boursière : {$alert->symbol}");
                }
            );
        } catch (\Exception $e) {
            \Log::error("Failed to send alert email: " . $e->getMessage());
        }
    }
}
