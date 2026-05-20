<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id', 'user_id', 'symbol', 'name', 'exchange',
        'quantity', 'avg_buy_price', 'current_price', 'previous_close',
        'price_updated_at', 'realized_pnl', 'is_open',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:6',
            'avg_buy_price' => 'decimal:4',
            'current_price' => 'decimal:4',
            'previous_close' => 'decimal:4',
            'realized_pnl' => 'decimal:2',
            'is_open' => 'boolean',
            'price_updated_at' => 'datetime',
        ];
    }

    public function portfolio(): BelongsTo { return $this->belongsTo(Portfolio::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function trades(): HasMany { return $this->hasMany(Trade::class); }

    public function currentValue(): float
    {
        return $this->quantity * ($this->current_price ?? $this->avg_buy_price);
    }

    public function costBasis(): float
    {
        return $this->quantity * $this->avg_buy_price;
    }

    public function unrealizedPnl(): float
    {
        return $this->currentValue() - $this->costBasis();
    }

    public function unrealizedPnlPercent(): float
    {
        if ($this->costBasis() == 0) return 0;
        return ($this->unrealizedPnl() / $this->costBasis()) * 100;
    }

    public function dayChange(): float
    {
        if (!$this->current_price || !$this->previous_close) return 0;
        return ($this->current_price - $this->previous_close) * $this->quantity;
    }

    public function dayChangePercent(): float
    {
        if (!$this->previous_close || $this->previous_close == 0) return 0;
        return (($this->current_price - $this->previous_close) / $this->previous_close) * 100;
    }
}
