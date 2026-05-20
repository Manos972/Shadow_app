<?php
namespace App\Livewire\Team;

use App\Models\Team;
use App\Models\TeamInvitation;
use Livewire\Component;
use Illuminate\Support\Facades\Mail;

class TeamSettings extends Component
{
    public string $teamName = '';
    public string $currency = 'CAD';
    public string $timezone = 'America/Toronto';
    public bool $showInviteModal = false;
    public string $inviteEmail = '';
    public string $inviteRole = 'member';

    public function mount(): void
    {
        $team = app('current_team');
        $this->teamName = $team->name;
        $this->currency = $team->currency;
        $this->timezone = $team->timezone;
    }

    public function saveSettings(): void
    {
        $this->validate([
            'teamName' => 'required|string|max:100',
            'currency' => 'required|in:CAD,USD,EUR,GBP',
            'timezone' => 'required|string',
        ]);

        $team = app('current_team');
        if ($team->owner_id !== auth()->id()) {
            session()->flash('error', 'Seul le propriétaire peut modifier les paramètres.');
            return;
        }

        $team->update([
            'name' => $this->teamName,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
        ]);

        session()->flash('success', 'Paramètres de l\'espace sauvegardés.');
    }

    public function sendInvitation(): void
    {
        $this->validate([
            'inviteEmail' => 'required|email',
            'inviteRole' => 'required|in:admin,member,viewer',
        ]);

        $team = app('current_team');

        if (!$team->canInvite()) {
            session()->flash('error', 'Limite de membres atteinte pour votre espace.');
            return;
        }

        $invitation = TeamInvitation::create([
            'team_id' => $team->id,
            'invited_by' => auth()->id(),
            'email' => $this->inviteEmail,
            'role' => $this->inviteRole,
        ]);

        // Send email notification
        try {
            Mail::raw(
                "Vous avez été invité(e) à rejoindre l'espace \"{$team->name}\" sur Shadow Finance.\n\n" .
                "Accepter l'invitation : " . url("/invitation/{$invitation->token}") . "\n\n" .
                "Cette invitation expire dans 7 jours.",
                fn($msg) => $msg->to($this->inviteEmail)->subject("Invitation à rejoindre {$team->name} sur Shadow Finance")
            );
        } catch (\Exception $e) {}

        $this->showInviteModal = false;
        $this->inviteEmail = '';
        session()->flash('success', "Invitation envoyée à {$this->inviteEmail}.");
    }

    public function removeMember(int $userId): void
    {
        $team = app('current_team');
        if ($team->owner_id !== auth()->id()) return;
        if ($userId === auth()->id()) return;
        $team->users()->detach($userId);
        session()->flash('success', 'Membre retiré de l\'espace.');
    }

    public function revokeInvitation(int $invitationId): void
    {
        TeamInvitation::where('id', $invitationId)->where('team_id', app('current_team')->id)->delete();
        session()->flash('success', 'Invitation révoquée.');
    }

    public function render()
    {
        $team = app('current_team');
        return view('livewire.team.team-settings', [
            'team' => $team,
            'members' => $team->users()->get(),
            'invitations' => $team->invitations()->where('accepted_at', null)->where('expires_at', '>', now())->get(),
            'isOwner' => $team->owner_id === auth()->id(),
        ]);
    }
}
