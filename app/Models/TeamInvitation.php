<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TeamInvitation extends Model
{
    protected $fillable = ['team_id', 'invited_by', 'email', 'role', 'token', 'expires_at', 'accepted_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TeamInvitation $inv) {
            $inv->token = Str::random(64);
            $inv->expires_at = now()->addDays(7);
        });
    }

    public function team(): BelongsTo { return $this->belongsTo(Team::class); }
    public function inviter(): BelongsTo { return $this->belongsTo(User::class, 'invited_by'); }

    public function isExpired(): bool { return $this->expires_at->isPast(); }
    public function isAccepted(): bool { return (bool) $this->accepted_at; }
    public function isPending(): bool { return !$this->isExpired() && !$this->isAccepted(); }
}
