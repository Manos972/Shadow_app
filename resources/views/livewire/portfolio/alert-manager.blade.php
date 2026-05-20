<div>
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-900/50 border border-green-500/50 p-3 text-sm text-green-300">{{ session('success') }}</div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-white">Alertes de prix</h2>
        <button wire:click="openModal()" class="btn-primary">+ Nouvelle alerte</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($alerts as $alert)
        <div class="card {{ $alert->triggered_at ? 'border-amber-500/40 bg-amber-900/10' : ($alert->is_active ? '' : 'opacity-60') }}">
            <div class="flex justify-between items-start">
                <div>
                    <div class="font-bold text-white">{{ $alert->symbol }}</div>
                    <div class="text-sm text-slate-400 mt-0.5">{{ $alert->getConditionLabel() }}</div>
                    @if($alert->triggered_at)
                        <div class="text-xs text-amber-400 mt-1">⚡ Déclenchée {{ $alert->triggered_at->diffForHumans() }}</div>
                    @elseif(!$alert->is_active)
                        <div class="text-xs text-slate-500 mt-1">Désactivée</div>
                    @else
                        <div class="text-xs text-emerald-500 mt-1">● Active</div>
                    @endif
                </div>
                <div class="flex gap-2">
                    @if($alert->triggered_at)
                        <button wire:click="reactivate({{ $alert->id }})" class="text-xs px-2 py-1 bg-blue-900/50 text-blue-400 rounded">↻ Réactiver</button>
                    @endif
                    <button wire:click="openModal({{ $alert->id }})" class="text-slate-400 hover:text-blue-400 text-sm">✏️</button>
                    <button wire:click="delete({{ $alert->id }})" wire:confirm="Supprimer ?" class="text-slate-400 hover:text-red-400 text-sm">🗑️</button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-2 text-center py-12 text-slate-500">
            Aucune alerte configurée. Créez des alertes pour être notifié aux moments clés.
        </div>
        @endforelse
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" wire:click.self="$set('showModal', false)">
        <div class="bg-dark-800 border border-dark-700 rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-white">{{ $editingId ? 'Modifier' : 'Nouvelle' }} alerte</h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="label">Symbole</label>
                    <input type="text" wire:model="symbol" class="input uppercase" placeholder="AAPL">
                    @error('symbol') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Condition</label>
                    <select wire:model="type" class="input">
                        <option value="price_above">Prix au-dessus de...</option>
                        <option value="price_below">Prix en-dessous de...</option>
                        <option value="change_percent_above">Variation > +X%</option>
                        <option value="change_percent_below">Variation < -X%</option>
                        <option value="rsi_above">RSI(14) > X (suracheté)</option>
                        <option value="rsi_below">RSI(14) < X (survendu)</option>
                    </select>
                </div>
                <div>
                    <label class="label">Seuil</label>
                    <input type="number" wire:model="threshold" step="0.01" class="input" placeholder="Ex: 150.00 ou 30 (RSI)">
                    @error('threshold') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" wire:model="notify_email" id="notify_email" class="rounded">
                    <label for="notify_email" class="text-sm text-slate-300 cursor-pointer">Notification par email</label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary flex-1">Enregistrer</button>
                    <button type="button" wire:click="$set('showModal', false)" class="btn-secondary flex-1">Annuler</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
