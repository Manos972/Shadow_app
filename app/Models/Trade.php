<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id', 'position_id', 'user_id', 'symbol',
        'type', 'quantity', 'price', 'fees', 'executed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:6',
            'price' => 'decimal:4',
            'fees' => 'decimal:2',
            'executed_at' => 'datetime',
        ];
    }

    public function portfolio(): BelongsTo { return $this->belongsTo(Portfolio::class); }
    public function position(): BelongsTo { return $this->belongsTo(Position::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function totalAmount(): float
    {
        return ($this->quantity * $this->price) + $this->fees;
    }
}
