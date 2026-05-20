<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'description', 'currency', 'color', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function positions(): HasMany { return $this->hasMany(Position::class); }
    public function trades(): HasMany { return $this->hasMany(Trade::class); }

    public function totalValue(): float
    {
        return $this->positions()->where('is_open', true)->get()->sum(function ($p) {
            return $p->quantity * ($p->current_price ?? $p->avg_buy_price);
        });
    }

    public function totalCost(): float
    {
        return $this->positions()->where('is_open', true)->sum(\DB::raw('quantity * avg_buy_price'));
    }

    public function unrealizedPnl(): float
    {
        return $this->positions()->where('is_open', true)->get()->sum(function ($p) {
            return $p->unrealizedPnl();
        });
    }

    public function realizedPnl(): float
    {
        return $this->positions()->sum('realized_pnl');
    }
}
