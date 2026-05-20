<?php
namespace App\Http\Controllers;

use App\Models\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamInvitationController extends Controller
{
    public function show(string $token)
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isExpired()) {
            return redirect('/login')->with('error', 'Cette invitation a expiré.');
        }
        if ($invitation->isAccepted()) {
            return redirect('/dashboard')->with('info', 'Invitation déjà utilisée.');
        }

        return view('invitation.show', compact('invitation'));
    }

    public function accept(string $token, Request $request): RedirectResponse
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isExpired() || $invitation->isAccepted()) {
            return redirect('/dashboard')->with('error', 'Invitation invalide ou expirée.');
        }

        $user = auth()->user();
        if (!$user) {
            session(['pending_invitation' => $token]);
            return redirect('/register')->with('info', 'Créez un compte pour accepter l\'invitation.');
        }

        $team = $invitation->team;
        if (!$team->hasUser($user)) {
            $team->users()->attach($user->id, ['role' => $invitation->role, 'joined_at' => now()]);
        }

        $invitation->update(['accepted_at' => now()]);
        $user->update(['current_team_id' => $team->id]);

        return redirect('/dashboard')->with('success', "Vous avez rejoint l'espace \"{$team->name}\" !");
    }
}
