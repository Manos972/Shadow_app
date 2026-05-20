<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dividend extends Model
{
    protected $fillable = [
        'user_id', 'portfolio_id', 'symbol', 'name', 'amount_per_share',
        'shares', 'currency', 'type', 'ex_date', 'pay_date',
        'is_drip', 'drip_shares', 'drip_price',
    ];

    protected function casts(): array
    {
        return [
            'ex_date' => 'date',
            'pay_date' => 'date',
            'is_drip' => 'boolean',
            'amount_per_share' => 'decimal:4',
            'shares' => 'decimal:6',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function portfolio(): BelongsTo { return $this->belongsTo(Portfolio::class); }

    public function totalAmount(): float { return $this->amount_per_share * $this->shares; }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'eligible' => 'Dividende admissible',
            'ordinary' => 'Dividende ordinaire',
            'return_of_capital' => 'Remboursement de capital',
            'capital_gain' => 'Gain en capital',
            default => $this->type,
        };
    }
}
