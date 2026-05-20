<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketDataCache extends Model
{
    protected $table = 'market_data_cache';

    protected $fillable = [
        'symbol', 'name', 'exchange', 'price', 'previous_close', 'open', 'high', 'low',
        'volume', 'market_cap', 'pe_ratio', 'eps', 'dividend_yield', 'beta',
        'week_52_high', 'week_52_low', 'price_history', 'rsi_14', 'sma_20', 'sma_50',
        'sma_200', 'macd', 'macd_signal', 'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:4',
            'previous_close' => 'decimal:4',
            'price_history' => 'array',
            'rsi_14' => 'decimal:4',
            'fetched_at' => 'datetime',
        ];
    }

    public function changeAmount(): float
    {
        if (!$this->price || !$this->previous_close) return 0;
        return $this->price - $this->previous_close;
    }

    public function changePercent(): float
    {
        if (!$this->previous_close || $this->previous_close == 0) return 0;
        return (($this->price - $this->previous_close) / $this->previous_close) * 100;
    }

    public function isStale(): bool
    {
        if (!$this->fetched_at) return true;
        return $this->fetched_at->diffInMinutes(now()) > 15;
    }
}
