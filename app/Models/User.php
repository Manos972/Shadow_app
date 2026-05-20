<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'currency', 'timezone', 'current_team_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ── Budget ───────────────────────────────────────────────

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    public function financialGoals(): HasMany
    {
        return $this->hasMany(FinancialGoal::class);
    }

    // ── Portfolio ────────────────────────────────────────────

    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function stockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function dividends(): HasMany
    {
        return $this->hasMany(Dividend::class);
    }

    // ── Teams ────────────────────────────────────────────────

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withPivot('role')->withTimestamps();
    }

    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function imports(): HasMany
    {
        return $this->hasMany(Import::class);
    }

    // ── Computed ─────────────────────────────────────────────

    public function totalNetWorth(): float
    {
        return $this->accounts()->where('is_active', true)->sum('balance');
    }

    public function totalPortfolioValue(): float
    {
        return $this->positions()->where('is_open', true)->get()->sum(function ($position) {
            return $position->quantity * ($position->current_price ?? $position->avg_buy_price);
        });
    }
}
