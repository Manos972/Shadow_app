<div class="space-y-6">
    @if(session('success'))
        <div class="glass-alert-success">✅ {{ session('success') }}</div>
    @endif

    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-white">Objectifs financiers</h2>
            <p class="text-xs text-slate-500 mt-0.5">CÉLI, REER, urgences, retraite...</p>
        </div>
        <button wire:click="openModal()" class="btn-primary">+ Nouvel objectif</button>
    </div>

    @if($goals->isEmpty())
    <div class="glass-card text-center py-12">
        <div class="text-5xl mb-4">🎯</div>
        <h3 class="font-semibold text-white mb-2">Définissez vos objectifs</h3>
        <p class="text-slate-400 text-sm mb-5">CÉLI, REER, fonds d'urgence, retraite — suivez votre progression.</p>
        <button wire:click="openModal()" class="btn-primary">Créer mon premier objectif</button>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($goals as $goal)
        <div class="glass-card group relative overflow-hidden" style="border-color: {{ $goal->color }}33">
            <div class="absolute inset-0 opacity-5 rounded-2xl" style="background: {{ $goal->color }}"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background: {{ $goal->color }}22">{{ $goal->icon }}</div>
                        <div>
                            <div class="font-semibold text-white text-sm">{{ $goal->name }}</div>
                            @if($goal->target_date)
                            <div class="text-xs text-slate-500">Objectif : {{ $goal->target_date->format('M Y') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click="openModal({{ $goal->id }})" class="p-1 text-slate-500 hover:text-blue-400">✏️</button>
                        <button wire:click="delete({{ $goal->id }})" wire:confirm="Supprimer ?" class="p-1 text-slate-500 hover:text-red-400">🗑️</button>
                    </div>
                </div>

                <div class="flex justify-between items-end mb-2">
                    <div>
                        <div class="text-2xl font-bold text-white">${{ number_format($goal->current_amount, 0, ',', ' ') }}</div>
                        <div class="text-xs text-slate-500">sur ${{ number_format($goal->target_amount, 0, ',', ' ') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xl font-bold" style="color: {{ $goal->color }}">{{ $goal->progressPercent() }}%</div>
                        @if($goal->monthsToGoal())
                        <div class="text-xs text-slate-500">{{ $goal->monthsToGoal() }} mois restants</div>
                        @endif
                    </div>
                </div>

                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $goal->progressPercent() }}%; background: linear-gradient(to right, {{ $goal->color }}, {{ $goal->color }}88)"></div>
                </div>

                @if($goal->monthly_contribution > 0)
                <div class="mt-2 text-xs text-slate-500">
                    Cotisation mensuelle : <span class="text-slate-300">${{ number_format($goal->monthly_contribution, 0, ',', ' ') }}/mois</span>
                </div>
                @endif

                @if($goal->remaining() > 0)
                <div class="mt-1 text-xs text-slate-500">
                    Restant : <span class="text-white font-medium">${{ number_format($goal->remaining(), 0, ',', ' ') }}</span>
                </div>
                @else
                <div class="mt-1">
                    <span class="badge badge-green">✅ Objectif atteint !</span>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- CÉLI 2025 tracker -->
    <div class="glass-card border-emerald-500/20">
        <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
            <span>🏦</span> Espace de cotisation CÉLI 2025
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div class="text-center p-3 glass-card-sm">
                <div class="text-slate-400 text-xs mb-1">Limite annuelle</div>
                <div class="font-bold text-white">7 000 $</div>
            </div>
            <div class="text-center p-3 glass-card-sm">
                <div class="text-slate-400 text-xs mb-1">Limite cumulative*</div>
                <div class="font-bold text-emerald-400">95 000 $</div>
            </div>
            <div class="text-center p-3 glass-card-sm">
                <div class="text-slate-400 text-xs mb-1">Taux d'inclusion gains</div>
                <div class="font-bold text-blue-400">0%</div>
            </div>
            <div class="text-center p-3 glass-card-sm">
                <div class="text-slate-400 text-xs mb-1">Retraits</div>
                <div class="font-bold text-violet-400">Libres d'impôt</div>
            </div>
        </div>
        <p class="text-xs text-slate-600 mt-3">* Cumul depuis 2009. Vérifiez votre espace exact sur Mon dossier CRA / ARC.</p>
    </div>
    @endif

    <!-- Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" wire:click.self="$set('showModal', false)">
        <div class="glass-card w-full max-w-lg shadow-2xl">
            <h3 class="text-lg font-bold text-white mb-5">{{ $editingId ? 'Modifier' : 'Nouvel' }} objectif</h3>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="label">Type d'objectif</label>
                    <select wire:model.live="type" class="input">
                        <option value="savings">💰 Épargne générale</option>
                        <option value="tfsa_max">🏦 Maximiser CÉLI</option>
                        <option value="rrsp_max">📊 Maximiser REER</option>
                        <option value="emergency_fund">🛡️ Fonds d'urgence</option>
                        <option value="retirement">🏖️ Retraite</option>
                        <option value="debt_payoff">💳 Rembourser une dette</option>
                        <option value="investment">📈 Investissement</option>
                        <option value="custom">🎯 Personnalisé</option>
                    </select>
                </div>
                <div>
                    <label class="label">Nom</label>
                    <input type="text" wire:model="name" class="input" placeholder="Fonds d'urgence 6 mois">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Montant cible ($)</label>
                        <input type="number" wire:model="target_amount" step="100" class="input" placeholder="10000">
                        @error('target_amount') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Montant actuel ($)</label>
                        <input type="number" wire:model="current_amount" step="100" class="input" placeholder="2500">
                    </div>
                    <div>
                        <label class="label">Cotisation mensuelle ($)</label>
                        <input type="number" wire:model="monthly_contribution" step="50" class="input" placeholder="500">
                    </div>
                    <div>
                        <label class="label">Date cible</label>
                        <input type="date" wire:model="target_date" class="input">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Emoji</label>
                        <input type="text" wire:model="icon" class="input text-center text-2xl" maxlength="4">
                    </div>
                    <div>
                        <label class="label">Couleur</label>
                        <input type="color" wire:model="color" class="input h-11 cursor-pointer">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary flex-1">Enregistrer</button>
                    <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Annuler</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
