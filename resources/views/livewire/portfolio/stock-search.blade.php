<div class="relative" x-data>
    <div class="card">
        <h3 class="font-semibold text-white mb-4">🔍 Recherche d'actions</h3>
        <div class="relative">
            <input type="text" wire:model.live.debounce.400ms="query" class="input pl-10" placeholder="Chercher AAPL, LVMH, BTC-EUR...">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
        </div>

        <!-- Résultats -->
        @if($loading)
            <div class="mt-3 text-center text-slate-400 text-sm py-4">Recherche...</div>
        @elseif(!empty($results))
        <div class="mt-2 bg-dark-900 rounded-xl border border-dark-700 overflow-hidden">
            @foreach($results as $r)
            <button wire:click="selectSymbol('{{ $r['symbol'] }}', '{{ addslashes($r['name']) }}')"
                class="w-full flex items-center justify-between px-4 py-3 hover:bg-dark-700 transition-colors text-left border-b border-dark-700/50 last:border-0">
                <div>
                    <div class="font-semibold text-white text-sm">{{ $r['symbol'] }}</div>
                    <div class="text-xs text-slate-400">{{ $r['name'] }}</div>
                </div>
                <div class="text-xs text-slate-500">{{ $r['exchange'] }}</div>
            </button>
            @endforeach
        </div>
        @endif

        <!-- Quote affiché -->
        @if($selectedQuote)
        <div class="mt-4 bg-dark-900 rounded-xl border border-dark-700 p-4">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <div class="text-xl font-bold text-white">{{ $selectedQuote['symbol'] }}</div>
                    <div class="text-sm text-slate-400">{{ $selectedQuote['name'] ?? '' }}</div>
                    <div class="text-xs text-slate-500">{{ $selectedQuote['exchange'] ?? '' }} · {{ $selectedQuote['currency'] ?? '' }}</div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-white">{{ number_format($selectedQuote['price'] ?? 0, 2) }}</div>
                    <div class="text-sm {{ ($selectedQuote['change_percent'] ?? 0) >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ ($selectedQuote['change_percent'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($selectedQuote['change_percent'] ?? 0, 2) }}%
                        ({{ ($selectedQuote['change'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($selectedQuote['change'] ?? 0, 2) }})
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs text-slate-400 border-t border-dark-700 pt-3">
                <div>Ouverture : <span class="text-white">{{ number_format($selectedQuote['day_high'] ?? 0, 2) }}</span></div>
                <div>Volume : <span class="text-white">{{ number_format($selectedQuote['volume'] ?? 0, 0, ',', ' ') }}</span></div>
                <div>52S haut : <span class="text-emerald-400">{{ number_format($selectedQuote['week_52_high'] ?? 0, 2) }}</span></div>
                <div>52S bas : <span class="text-red-400">{{ number_format($selectedQuote['week_52_low'] ?? 0, 2) }}</span></div>
            </div>
            <div class="mt-3 flex gap-2">
                <button onclick="Livewire.dispatch('open-buy-{{ $selectedQuote['symbol'] }}')" class="btn-primary flex-1 text-sm">📈 Acheter</button>
                <button wire:click="$set('selectedQuote', null)" class="btn-secondary text-sm px-3">✕</button>
            </div>
        </div>
        @endif
    </div>
</div>
