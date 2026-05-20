<div>
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-900/50 border border-green-500/50 p-3 text-sm text-green-300">{{ session('success') }}</div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-white">Liste de surveillance</h2>
        <div class="flex gap-2">
            <button wire:click="refreshQuotes" class="btn-secondary text-sm">🔄 Actualiser</button>
            <button wire:click="$set('showAddModal', true)" class="btn-primary">+ Ajouter</button>
        </div>
    </div>

    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-dark-700">
                    <tr>
                        <th class="table-th">Symbole</th>
                        <th class="table-th">Prix actuel</th>
                        <th class="table-th text-right">Variation</th>
                        <th class="table-th text-right">Objectif</th>
                        <th class="table-th text-center">Upside</th>
                        <th class="table-th"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse($watchlist as $item)
                    @php $q = $quotes[$item->symbol] ?? null; @endphp
                    <tr class="hover:bg-dark-700/30 transition-colors group">
                        <td class="table-td">
                            <div class="font-bold text-white">{{ $item->symbol }}</div>
                            <div class="text-xs text-slate-500">{{ $item->name }} · {{ $item->exchange }}</div>
                        </td>
                        <td class="table-td font-semibold text-white">
                            {{ $q ? number_format($q['price'], 2) : '—' }}
                        </td>
                        <td class="table-td text-right">
                            @if($q)
                                <span class="{{ ($q['change_percent'] ?? 0) >= 0 ? 'text-emerald-400' : 'text-red-400' }} text-sm font-medium">
                                    {{ ($q['change_percent'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($q['change_percent'] ?? 0, 2) }}%
                                </span>
                            @else
                                <span class="text-slate-600">—</span>
                            @endif
                        </td>
                        <td class="table-td text-right text-slate-400">
                            {{ $item->target_price ? number_format($item->target_price, 2) : '—' }}
                        </td>
                        <td class="table-td text-center">
                            @if($item->target_price && $q)
                                @php $upside = (($item->target_price - $q['price']) / $q['price']) * 100 @endphp
                                <span class="text-xs font-semibold {{ $upside >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $upside >= 0 ? '+' : '' }}{{ number_format($upside, 1) }}%
                                </span>
                            @endif
                        </td>
                        <td class="table-td">
                            <button wire:click="remove({{ $item->id }})" wire:confirm="Retirer de la watchlist ?" class="text-slate-500 hover:text-red-400 opacity-0 group-hover:opacity-100 transition-all">🗑️</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-12 text-slate-500">Watchlist vide. Ajoutez des actions à surveiller.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showAddModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" wire:click.self="$set('showAddModal', false)">
        <div class="bg-dark-800 border border-dark-700 rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-white">Ajouter à la watchlist</h3>
                <button wire:click="$set('showAddModal', false)" class="text-slate-400 hover:text-white">✕</button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="label">Chercher un titre</label>
                    <input type="text" wire:model.live.debounce.400ms="searchQuery" class="input" placeholder="Apple, LVMH, BTC...">
                </div>
                @if(!empty($searchResults))
                <div class="bg-dark-900 rounded-xl border border-dark-700 overflow-hidden max-h-48 overflow-y-auto">
                    @foreach($searchResults as $r)
                    <button wire:click="prepareAdd('{{ $r['symbol'] }}', '{{ addslashes($r['name']) }}', '{{ $r['exchange'] ?? '' }}')"
                        class="w-full flex justify-between px-4 py-3 hover:bg-dark-700 transition-colors text-left border-b border-dark-700/50 last:border-0">
                        <div>
                            <div class="font-semibold text-white text-sm">{{ $r['symbol'] }}</div>
                            <div class="text-xs text-slate-400">{{ $r['name'] }}</div>
                        </div>
                        <span class="text-xs text-slate-500">{{ $r['exchange'] ?? '' }}</span>
                    </button>
                    @endforeach
                </div>
                @endif
                @if($addingSymbol)
                <div class="bg-dark-900 rounded-xl p-3 text-sm">
                    <span class="font-bold text-white">{{ $addingSymbol }}</span> — {{ $addingName }}
                </div>
                <div>
                    <label class="label">Prix objectif (optionnel)</label>
                    <input type="number" wire:model="targetPrice" step="0.01" class="input" placeholder="Ex: 180.00">
                </div>
                <div>
                    <label class="label">Notes</label>
                    <textarea wire:model="notes" class="input" rows="2"></textarea>
                </div>
                <button wire:click="add" class="btn-primary w-full">Ajouter à la watchlist</button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
