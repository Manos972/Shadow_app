<div class="space-y-6">
    @if(session('success'))
        <div class="glass-alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="glass-alert-error">❌ {{ session('error') }}</div>
    @endif

    <!-- Team Info -->
    <div class="glass-card">
        <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center text-blue-400">🏠</span>
            Mon espace financier
        </h2>
        @if($isOwner)
        <form wire:submit="saveSettings" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Nom de l'espace</label>
                    <input type="text" wire:model="teamName" class="input" placeholder="Ma famille, Mon foyer...">
                </div>
                <div>
                    <label class="label">Devise principale</label>
                    <select wire:model="currency" class="input">
                        <option value="CAD">🇨🇦 Dollar canadien (CAD)</option>
                        <option value="USD">🇺🇸 Dollar américain (USD)</option>
                        <option value="EUR">🇪🇺 Euro (EUR)</option>
                    </select>
                </div>
                <div>
                    <label class="label">Fuseau horaire</label>
                    <select wire:model="timezone" class="input">
                        <option value="America/Toronto">Est (Toronto, Montréal)</option>
                        <option value="America/Winnipeg">Centre (Winnipeg)</option>
                        <option value="America/Edmonton">Montagne (Calgary)</option>
                        <option value="America/Vancouver">Pacifique (Vancouver)</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-primary">Sauvegarder</button>
        </form>
        @endif
    </div>

    <!-- Members -->
    <div class="glass-card">
        <div class="flex justify-between items-center mb-5">
            <h3 class="font-semibold text-white flex items-center gap-2">
                <span>👥</span> Membres ({{ $members->count() }}/{{ $team->max_members }})
            </h3>
            @if($isOwner && $team->canInvite())
            <button wire:click="$set('showInviteModal', true)" class="btn-primary text-sm">+ Inviter</button>
            @endif
        </div>

        <div class="space-y-3">
            @foreach($members as $member)
            <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-violet-500 flex items-center justify-center text-white font-bold text-sm">
                        {{ substr($member->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="text-white font-medium text-sm">{{ $member->name }}</div>
                        <div class="text-xs text-slate-400">{{ $member->email }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $member->pivot->role === 'owner' ? 'bg-amber-500/20 text-amber-300' : 'bg-blue-500/20 text-blue-300' }}">
                        {{ ucfirst($member->pivot->role) }}
                    </span>
                    @if($isOwner && $member->id !== auth()->id())
                    <button wire:click="removeMember({{ $member->id }})" wire:confirm="Retirer ce membre ?" class="text-slate-500 hover:text-red-400 text-sm">✕</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @if($invitations->isNotEmpty())
        <div class="mt-4 pt-4 border-t border-white/10">
            <div class="text-xs text-slate-400 mb-3">Invitations en attente</div>
            @foreach($invitations as $inv)
            <div class="flex items-center justify-between p-2 text-sm">
                <div>
                    <span class="text-slate-300">{{ $inv->email }}</span>
                    <span class="text-xs text-slate-500 ml-2">· {{ $inv->role }} · expire {{ $inv->expires_at->diffForHumans() }}</span>
                </div>
                <button wire:click="revokeInvitation({{ $inv->id }})" class="text-xs text-red-400 hover:text-red-300">Révoquer</button>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Roles explainer -->
    <div class="glass-card">
        <h3 class="font-semibold text-white mb-3">Niveaux d'accès</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
            <div class="p-3 bg-white/5 rounded-xl">
                <div class="font-medium text-amber-300 mb-1">👑 Admin</div>
                <div class="text-slate-400 text-xs">Peut tout voir et modifier, sauf supprimer l'espace</div>
            </div>
            <div class="p-3 bg-white/5 rounded-xl">
                <div class="font-medium text-blue-300 mb-1">✏️ Membre</div>
                <div class="text-slate-400 text-xs">Peut ajouter des transactions et gérer son budget</div>
            </div>
            <div class="p-3 bg-white/5 rounded-xl">
                <div class="font-medium text-slate-300 mb-1">👁️ Lecteur</div>
                <div class="text-slate-400 text-xs">Consultation uniquement, pas de modifications</div>
            </div>
        </div>
    </div>

    <!-- Invite Modal -->
    @if($showInviteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" wire:click.self="$set('showInviteModal', false)">
        <div class="glass-card w-full max-w-md shadow-2xl">
            <h3 class="text-lg font-bold text-white mb-4">Inviter quelqu'un</h3>
            <form wire:submit="sendInvitation" class="space-y-4">
                <div>
                    <label class="label">Adresse email</label>
                    <input type="email" wire:model="inviteEmail" class="input" placeholder="partenaire@email.com">
                    @error('inviteEmail') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Rôle</label>
                    <select wire:model="inviteRole" class="input">
                        <option value="admin">Admin — accès complet</option>
                        <option value="member">Membre — peut modifier</option>
                        <option value="viewer">Lecteur — consultation seulement</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary flex-1">Envoyer l'invitation</button>
                    <button type="button" wire:click="$set('showInviteModal', false)" class="btn-secondary">Annuler</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
