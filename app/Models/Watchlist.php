<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Watchlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'symbol', 'name', 'exchange', 'notes', 'target_price',
    ];

    protected function casts(): array
    {
        return ['target_price' => 'decimal:4'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
