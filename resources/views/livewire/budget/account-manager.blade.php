<div>
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-900/50 border border-green-500/50 p-3 text-sm text-green-300">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-900/50 border border-red-500/50 p-3 text-sm text-red-300">{{ session('error') }}</div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-white">Mes Comptes</h2>
        <button wire:click="openModal()" class="btn-primary">+ Nouveau compte</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($accounts as $account)
        <div class="card group hover:border-blue-500/50 transition-all">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background: {{ $account->color }}22; border: 1px solid {{ $account->color }}55">
                        {{ $account->type_icon }}
                    </div>
                    <div>
                        <div class="font-semibold text-white">{{ $account->name }}</div>
                        <div class="text-xs text-slate-400 capitalize">{{ __('account.'.$account->type) ?? $account->type }}</div>
                    </div>
                </div>
                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button wire:click="openModal({{ $account->id }})" class="p-1.5 text-slate-400 hover:text-blue-400 rounded">✏️</button>
                    <button wire:click="delete({{ $account->id }})" wire:confirm="Supprimer ce compte ?" class="p-1.5 text-slate-400 hover:text-red-400 rounded">🗑️</button>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold" style="color: {{ $account->color }}">
                    {{ number_format($account->balance, 2, ',', ' ') }} {{ $account->currency }}
                </div>
                <div class="text-xs text-slate-500 mt-1">{{ $account->transactions_count }} transactions</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Total par type -->
    <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card text-center">
            <div class="text-xs text-slate-400 mb-1">Courants</div>
            <div class="text-lg font-bold text-blue-400">{{ number_format($totals['checking'], 0, ',', ' ') }} €</div>
        </div>
        <div class="card text-center">
            <div class="text-xs text-slate-400 mb-1">Épargne</div>
            <div class="text-lg font-bold text-emerald-400">{{ number_format($totals['savings'], 0, ',', ' ') }} €</div>
        </div>
        <div class="card text-center">
            <div class="text-xs text-slate-400 mb-1">Espèces</div>
            <div class="text-lg font-bold text-amber-400">{{ number_format($totals['cash'], 0, ',', ' ') }} €</div>
        </div>
        <div class="card text-center">
            <div class="text-xs text-slate-400 mb-1">Crédit</div>
            <div class="text-lg font-bold text-red-400">{{ number_format($totals['credit'], 0, ',', ' ') }} €</div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" wire:click.self="$set('showModal', false)">
        <div class="bg-dark-800 border border-dark-700 rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-white">{{ $editingId ? 'Modifier' : 'Nouveau' }} compte</h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="label">Nom</label>
                    <input type="text" wire:model="name" class="input" placeholder="Ex: Compte BNP">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Type</label>
                    <select wire:model="type" class="input">
                        <option value="checking">🏦 Compte courant</option>
                        <option value="savings">💰 Épargne / Livret</option>
                        <option value="cash">💵 Espèces</option>
                        <option value="credit">💳 Carte de crédit</option>
                        <option value="investment">📈 Investissement</option>
                        <option value="crypto">₿ Crypto</option>
                    </select>
                </div>
                @if(!$editingId)
                <div>
                    <label class="label">Solde initial</label>
                    <input type="number" wire:model="balance" step="0.01" class="input" placeholder="0.00">
                    @error('balance') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @endif
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Devise</label>
                        <select wire:model="currency" class="input">
                            <option value="EUR">EUR €</option>
                            <option value="USD">USD $</option>
                            <option value="GBP">GBP £</option>
                            <option value="CHF">CHF ₣</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Couleur</label>
                        <input type="color" wire:model="color" class="input h-10 cursor-pointer">
                    </div>
                </div>
                <div>
                    <label class="label">Notes (optionnel)</label>
                    <textarea wire:model="notes" class="input" rows="2"></textarea>
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
