<?php
namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Illuminate\Http\Request;

class SetCurrentTeam
{
    public function handle(Request $request, Closure $next)
    {
        if ($user = $request->user()) {
            $team = $user->currentTeam;

            if (!$team) {
                // Create a personal team if none exists
                $team = Team::create([
                    'name' => $user->name . "'s Space",
                    'owner_id' => $user->id,
                    'currency' => $user->currency ?? 'CAD',
                ]);
                $team->users()->attach($user->id, ['role' => 'owner', 'joined_at' => now()]);
                $user->update(['current_team_id' => $team->id]);
            }

            app()->instance('current_team', $team);
            view()->share('currentTeam', $team);
        }

        return $next($request);
    }
}
