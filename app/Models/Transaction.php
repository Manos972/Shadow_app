<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'account_id', 'category_id', 'transfer_account_id',
        'type', 'amount', 'description', 'notes', 'date', 'is_reconciled', 'recurring_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
            'is_reconciled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Transaction $transaction) {
            $account = $transaction->account;
            if ($transaction->type === 'income') {
                $account->increment('balance', $transaction->amount);
            } elseif ($transaction->type === 'expense') {
                $account->decrement('balance', $transaction->amount);
            } elseif ($transaction->type === 'transfer') {
                $account->decrement('balance', $transaction->amount);
                if ($transaction->transfer_account_id) {
                    Account::find($transaction->transfer_account_id)->increment('balance', $transaction->amount);
                }
            }
        });

        static::deleted(function (Transaction $transaction) {
            $account = $transaction->account;
            if ($transaction->type === 'income') {
                $account->decrement('balance', $transaction->amount);
            } elseif ($transaction->type === 'expense') {
                $account->increment('balance', $transaction->amount);
            }
        });
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function transferAccount(): BelongsTo { return $this->belongsTo(Account::class, 'transfer_account_id'); }
}
