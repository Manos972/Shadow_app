<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'symbol', 'name', 'type', 'threshold',
        'is_active', 'notify_email', 'triggered_at',
    ];

    protected function casts(): array
    {
        return [
            'threshold' => 'decimal:4',
            'is_active' => 'boolean',
            'notify_email' => 'boolean',
            'triggered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function getConditionLabel(): string
    {
        return match($this->type) {
            'price_above' => "Prix au-dessus de {$this->threshold}",
            'price_below' => "Prix en-dessous de {$this->threshold}",
            'change_percent_above' => "Variation > +{$this->threshold}%",
            'change_percent_below' => "Variation < -{$this->threshold}%",
            'rsi_above' => "RSI(14) > {$this->threshold} (suracheté)",
            'rsi_below' => "RSI(14) < {$this->threshold} (survendu)",
            default => $this->type,
        };
    }
}
