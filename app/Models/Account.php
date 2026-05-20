<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'type', 'balance', 'initial_balance',
        'currency', 'color', 'icon', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'initial_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'checking' => '🏦',
            'savings' => '💰',
            'cash' => '💵',
            'credit' => '💳',
            'investment' => '📈',
            'crypto' => '₿',
            default => '🏦',
        };
    }
}
