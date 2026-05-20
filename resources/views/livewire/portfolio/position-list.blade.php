<div>
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-900/50 border border-green-500/50 p-3 text-sm text-green-300">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap gap-3 items-center justify-between mb-4">
        <div class="flex gap-2 items-center">
            <select wire:model.live="portfolioId" class="input w-auto">
                <option value="">Tous les portefeuilles</option>
                @foreach($portfolios as $pf)
                    <option value="{{ $pf->id }}">{{ $pf->name }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 text-sm text-slate-400 cursor-pointer">
                <input type="checkbox" wire:model.live="showClosed" class="rounded"> Positions fermées
            </label>
        </div>
        <div class="flex gap-2">
            <button wire:click="refreshPrices" class="btn-secondary text-sm">🔄 Actualiser</button>
            <livewire:portfolio.trade-form />
        </div>
    </div>

    <!-- Résumé -->
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="card text-center">
            <div class="text-xs text-slate-400 mb-1">Valeur totale</div>
            <div class="text-lg font-bold text-white">{{ number_format($totalValue, 2, ',', ' ') }} €</div>
        </div>
        <div class="card text-center">
            <div class="text-xs text-slate-400 mb-1">P&L latent</div>
            <div class="text-lg font-bold {{ $totalPnl >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $totalPnl >= 0 ? '+' : '' }}{{ number_format($totalPnl, 2, ',', ' ') }} €
            </div>
        </div>
        <div class="card text-center">
            <div class="text-xs text-slate-400 mb-1">Var. journalière</div>
            <div class="text-lg font-bold {{ $totalDayChange >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $totalDayChange >= 0 ? '+' : '' }}{{ number_format($totalDayChange, 2, ',', ' ') }} €
            </div>
        </div>
    </div>

    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-dark-700">
                    <tr>
                        <th class="table-th cursor-pointer" wire:click="sort('symbol')">Symbole</th>
                        <th class="table-th">Quantité</th>
                        <th class="table-th">PRU</th>
                        <th class="table-th">Prix actuel</th>
                        <th class="table-th cursor-pointer text-right" wire:click="sort('value')">Valeur</th>
                        <th class="table-th cursor-pointer text-right" wire:click="sort('pnl')">P&L</th>
                        <th class="table-th cursor-pointer text-right" wire:click="sort('day_change')">Jour</th>
                        <th class="table-th">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse($positions as $pos)
                    <tr class="hover:bg-dark-700/30 transition-colors group">
                        <td class="table-td">
                            <div class="font-bold text-white">{{ $pos->symbol }}</div>
                            <div class="text-xs text-slate-500">{{ Str::limit($pos->name, 20) }}</div>
                        </td>
                        <td class="table-td text-slate-300">{{ number_format($pos->quantity, 4) }}</td>
                        <td class="table-td text-slate-400">{{ number_format($pos->avg_buy_price, 2) }}</td>
                        <td class="table-td">
                            @if($pos->current_price)
                                <div class="font-semibold text-white">{{ number_format($pos->current_price, 2) }}</div>
                                @if($pos->price_updated_at)
                                    <div class="text-xs text-slate-600">{{ $pos->price_updated_at->diffForHumans() }}</div>
                                @endif
                            @else
                                <span class="text-slate-600 text-xs">—</span>
                            @endif
                        </td>
                        <td class="table-td text-right font-semibold text-white">{{ number_format($pos->currentValue(), 2, ',', ' ') }} €</td>
                        <td class="table-td text-right">
                            <div class="font-semibold {{ $pos->unrealizedPnl() >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $pos->unrealizedPnl() >= 0 ? '+' : '' }}{{ number_format($pos->unrealizedPnl(), 2, ',', ' ') }} €
                            </div>
                            <div class="text-xs {{ $pos->unrealizedPnlPercent() >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $pos->unrealizedPnlPercent() >= 0 ? '+' : '' }}{{ number_format($pos->unrealizedPnlPercent(), 2) }}%
                            </div>
                        </td>
                        <td class="table-td text-right {{ $pos->dayChangePercent() >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $pos->dayChangePercent() >= 0 ? '+' : '' }}{{ number_format($pos->dayChangePercent(), 2) }}%
                        </td>
                        <td class="table-td">
                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button onclick="Livewire.dispatch('open-trade-buy', { symbol: '{{ $pos->symbol }}' })" class="text-xs px-2 py-1 bg-emerald-900/50 text-emerald-400 rounded hover:bg-emerald-800">Achat</button>
                                <button onclick="Livewire.dispatch('open-trade-sell', { symbol: '{{ $pos->symbol }}' })" class="text-xs px-2 py-1 bg-red-900/50 text-red-400 rounded hover:bg-red-800">Vente</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-12 text-slate-500">Aucune position. Enregistrez votre premier achat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
