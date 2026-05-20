<div>
    <button wire:click="openModal('buy')" class="btn-primary">+ Nouvel ordre</button>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" wire:click.self="$set('showModal', false)">
        <div class="bg-dark-800 border border-dark-700 rounded-2xl shadow-2xl w-full max-w-lg p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold text-white">Enregistrer un ordre</h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <!-- Buy/Sell tabs -->
            <div class="flex gap-1 bg-dark-900 rounded-xl p-1 mb-5">
                <button wire:click="$set('type', 'buy')" class="flex-1 py-2 rounded-lg text-sm font-medium transition-all {{ $type === 'buy' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-white' }}">📈 Achat</button>
                <button wire:click="$set('type', 'sell')" class="flex-1 py-2 rounded-lg text-sm font-medium transition-all {{ $type === 'sell' ? 'bg-red-600 text-white' : 'text-slate-400 hover:text-white' }}">📉 Vente</button>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="label">Portefeuille</label>
                    <select wire:model="portfolio_id" class="input">
                        @foreach($portfolios as $pf)
                            <option value="{{ $pf->id }}">{{ $pf->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="label">Symbole</label>
                        <div class="flex gap-2">
                            <input type="text" wire:model="symbol" class="input flex-1 uppercase" placeholder="AAPL">
                            <button type="button" wire:click="fetchPrice" class="px-3 bg-dark-700 hover:bg-dark-600 rounded-xl text-sm transition-colors" :class="{ 'animate-pulse': $wire.loadingPrice }">🔍</button>
                        </div>
                        @error('symbol') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Nom</label>
                        <input type="text" wire:model="name" class="input" placeholder="Apple Inc.">
                    </div>
                </div>

                @if($currentQuote)
                <div class="bg-dark-900 rounded-xl p-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-400">{{ $currentQuote['name'] ?? $symbol }}</span>
                        <span class="font-bold text-white">{{ $currentQuote['price'] ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-xs text-slate-500">{{ $currentQuote['exchange'] ?? '' }}</span>
                        <span class="text-xs {{ ($currentQuote['change_percent'] ?? 0) >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ ($currentQuote['change_percent'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($currentQuote['change_percent'] ?? 0, 2) }}%
                        </span>
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="label">Quantité</label>
                        <input type="number" wire:model="quantity" step="0.000001" min="0" class="input" placeholder="10">
                        @error('quantity') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Prix unitaire</label>
                        <input type="number" wire:model="price" step="0.0001" min="0" class="input" placeholder="0.00">
                        @error('price') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Frais</label>
                        <input type="number" wire:model="fees" step="0.01" min="0" class="input" placeholder="0.00">
                    </div>
                </div>

                @if($quantity && $price)
                <div class="bg-dark-900 rounded-xl p-3 text-sm text-slate-300">
                    Total : <span class="font-bold text-white">{{ number_format((float)$quantity * (float)$price + (float)$fees, 2, ',', ' ') }} €</span>
                </div>
                @endif

                <div>
                    <label class="label">Date d'exécution</label>
                    <input type="datetime-local" wire:model="executed_at" class="input">
                    @error('executed_at') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Notes (optionnel)</label>
                    <textarea wire:model="notes" class="input" rows="2" placeholder="Raison de l'ordre..."></textarea>
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
