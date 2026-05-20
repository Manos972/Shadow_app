<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Team extends Model
{
    protected $fillable = ['name', 'slug', 'owner_id', 'currency', 'locale', 'timezone', 'plan', 'max_members', 'settings'];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    protected static function booted(): void
    {
        static::creating(function (Team $team) {
            if (!$team->slug) {
                $team->slug = Str::slug($team->name) . '-' . Str::random(6);
            }
        });
    }

    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_id'); }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function invitations(): HasMany { return $this->hasMany(TeamInvitation::class); }

    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function userRole(User $user): ?string
    {
        if ($this->owner_id === $user->id) return 'owner';
        return $this->users()->where('user_id', $user->id)->first()?->pivot->role;
    }

    public function canInvite(): bool
    {
        return $this->users()->count() < $this->max_members;
    }
}
