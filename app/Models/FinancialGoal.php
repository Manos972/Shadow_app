<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialGoal extends Model
{
    protected $fillable = [
        'user_id', 'name', 'type', 'target_amount', 'current_amount',
        'monthly_contribution', 'target_date', 'icon', 'color', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'monthly_contribution' => 'decimal:2',
            'target_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function progressPercent(): float
    {
        if ($this->target_amount <= 0) return 0;
        return min(round(($this->current_amount / $this->target_amount) * 100, 1), 100);
    }

    public function remaining(): float { return max($this->target_amount - $this->current_amount, 0); }

    public function monthsToGoal(): ?int
    {
        if ($this->monthly_contribution <= 0 || $this->remaining() <= 0) return null;
        return (int) ceil($this->remaining() / $this->monthly_contribution);
    }
}
