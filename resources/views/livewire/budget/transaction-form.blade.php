<div>
    <button wire:click="openModal()" class="btn-primary">+ Ajouter une transaction</button>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" wire:click.self="$set('showModal', false)">
        <div class="bg-dark-800 border border-dark-700 rounded-2xl shadow-2xl w-full max-w-lg p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold text-white">{{ $editingId ? 'Modifier' : 'Nouvelle' }} transaction</h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <!-- Type tabs -->
            <div class="flex gap-1 bg-dark-900 rounded-xl p-1 mb-5">
                <button wire:click="$set('type', 'expense')" class="flex-1 py-2 rounded-lg text-sm font-medium transition-all {{ $type === 'expense' ? 'bg-red-500 text-white' : 'text-slate-400 hover:text-white' }}">Dépense</button>
                <button wire:click="$set('type', 'income')" class="flex-1 py-2 rounded-lg text-sm font-medium transition-all {{ $type === 'income' ? 'bg-emerald-500 text-white' : 'text-slate-400 hover:text-white' }}">Revenu</button>
                <button wire:click="$set('type', 'transfer')" class="flex-1 py-2 rounded-lg text-sm font-medium transition-all {{ $type === 'transfer' ? 'bg-blue-500 text-white' : 'text-slate-400 hover:text-white' }}">Virement</button>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2 md:col-span-1">
                        <label class="label">Montant</label>
                        <div class="relative">
                            <input type="number" wire:model="amount" step="0.01" min="0.01" class="input pr-8" placeholder="0.00">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">€</span>
                        </div>
                        @error('amount') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <label class="label">Date</label>
                        <input type="date" wire:model="date" class="input">
                        @error('date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="label">Description</label>
                    <input type="text" wire:model="description" class="input" placeholder="Ex: Courses Carrefour">
                    @error('description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label">Compte {{ $type === 'transfer' ? 'source' : '' }}</label>
                    <select wire:model="account_id" class="input">
                        <option value="">Sélectionner un compte</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->type_icon }} {{ $acc->name }} — {{ number_format($acc->balance, 0, ',', ' ') }} €</option>
                        @endforeach
                    </select>
                    @error('account_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if($type === 'transfer')
                <div>
                    <label class="label">Compte destination</label>
                    <select wire:model="transfer_account_id" class="input">
                        <option value="">Sélectionner un compte</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->type_icon }} {{ $acc->name }}</option>
                        @endforeach
                    </select>
                    @error('transfer_account_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @else
                <div>
                    <label class="label">Catégorie</label>
                    <select wire:model="category_id" class="input">
                        <option value="">Sans catégorie</option>
                        @foreach($type === 'income' ? $incomeCategories : $expenseCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->icon }} {{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="label">Notes (optionnel)</label>
                    <textarea wire:model="notes" class="input" rows="2" placeholder="Détails..."></textarea>
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
