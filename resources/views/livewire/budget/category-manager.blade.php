<div>
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-900/50 border border-green-500/50 p-3 text-sm text-green-300">{{ session('success') }}</div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div class="flex gap-2">
            <button wire:click="$set('activeTab', 'expense')" class="tab-btn {{ $activeTab === 'expense' ? 'tab-active' : '' }}">Dépenses</button>
            <button wire:click="$set('activeTab', 'income')" class="tab-btn {{ $activeTab === 'income' ? 'tab-active' : '' }}">Revenus</button>
        </div>
        <button wire:click="openModal(null, '{{ $activeTab }}')" class="btn-primary">+ Nouvelle catégorie</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($activeTab === 'expense' ? $expenseCategories : $incomeCategories as $cat)
        <div class="card group flex items-center justify-between hover:border-slate-600 transition-all">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background: {{ $cat->color }}22; border: 1px solid {{ $cat->color }}44">
                    {{ $cat->icon }}
                </div>
                <div>
                    <div class="font-medium text-white">{{ $cat->name }}</div>
                    @if($cat->monthly_budget)
                        <div class="text-xs text-slate-400">Budget: {{ number_format($cat->monthly_budget, 0, ',', ' ') }} €/mois</div>
                    @endif
                </div>
            </div>
            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button wire:click="openModal({{ $cat->id }})" class="p-1.5 text-slate-400 hover:text-blue-400">✏️</button>
                <button wire:click="delete({{ $cat->id }})" wire:confirm="Supprimer ?" class="p-1.5 text-slate-400 hover:text-red-400">🗑️</button>
            </div>
        </div>
        @endforeach
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" wire:click.self="$set('showModal', false)">
        <div class="bg-dark-800 border border-dark-700 rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-white">{{ $editingId ? 'Modifier' : 'Nouvelle' }} catégorie</h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="label">Nom</label>
                    <input type="text" wire:model="name" class="input" placeholder="Ex: Alimentation">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Type</label>
                    <select wire:model="type" class="input">
                        <option value="expense">Dépense</option>
                        <option value="income">Revenu</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Emoji icône</label>
                        <input type="text" wire:model="icon" class="input text-2xl text-center" maxlength="4" placeholder="🏷️">
                        @error('icon') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Couleur</label>
                        <input type="color" wire:model="color" class="input h-10 cursor-pointer">
                    </div>
                </div>
                <div>
                    <label class="label">Budget mensuel (€, optionnel)</label>
                    <input type="number" wire:model="monthly_budget" step="0.01" min="0" class="input" placeholder="0.00">
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
