<div class="space-y-6">
    <!-- Sélecteur de portefeuille -->
    @if($portfolios->count() > 1)
    <div class="flex gap-2 flex-wrap">
        @foreach($portfolios as $pf)
        <button wire:click="$set('selectedPortfolioId', {{ $pf->id }})"
            class="px-4 py-2 rounded-xl text-sm font-medium transition-all border {{ $selectedPortfolioId == $pf->id ? 'border-transparent text-white' : 'border-dark-600 text-slate-400 hover:text-white' }}"
            style="{{ $selectedPortfolioId == $pf->id ? 'background:' . $pf->color . '33; border-color:' . $pf->color . '88' : '' }}">
            {{ $pf->name }}
        </button>
        @endforeach
    </div>
    @endif

    @if($portfolio)
    <!-- KPIs -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card col-span-2 bg-gradient-to-br from-violet-900/40 to-violet-800/10 border-violet-700/50">
            <div class="text-xs text-violet-300 mb-1">Valeur du portefeuille</div>
            <div class="text-3xl font-bold text-white">{{ number_format($totalValue, 2, ',', ' ') }} €</div>
            <div class="text-xs text-slate-400 mt-1">Coût total : {{ number_format($totalCost, 2, ',', ' ') }} €</div>
        </div>
        <div class="card">
            <div class="text-xs text-slate-400 mb-1">Plus-value latente</div>
            <div class="text-xl font-bold {{ $unrealizedPnl >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $unrealizedPnl >= 0 ? '+' : '' }}{{ number_format($unrealizedPnl, 2, ',', ' ') }} €
            </div>
            @if($totalCost > 0)
            <div class="text-xs {{ $unrealizedPnl >= 0 ? 'text-emerald-500' : 'text-red-500' }} mt-0.5">
                {{ $unrealizedPnl >= 0 ? '+' : '' }}{{ number_format(($unrealizedPnl / $totalCost) * 100, 2) }}%
            </div>
            @endif
        </div>
        <div class="card">
            <div class="text-xs text-slate-400 mb-1">Plus-value réalisée</div>
            <div class="text-xl font-bold {{ $realizedPnl >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $realizedPnl >= 0 ? '+' : '' }}{{ number_format($realizedPnl, 2, ',', ' ') }} €
            </div>
        </div>
    </div>

    <!-- Top / Worst performers -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card">
            <h3 class="font-semibold text-white mb-3">🚀 Meilleures performances</h3>
            <div class="space-y-2">
                @foreach($topPerformers as $pos)
                <div class="flex justify-between items-center">
                    <div>
                        <span class="font-semibold text-white text-sm">{{ $pos['symbol'] }}</span>
                        <span class="text-slate-500 text-xs ml-2">{{ Str::limit($pos['name'], 20) }}</span>
                    </div>
                    <div class="text-right">
                        <div class="text-emerald-400 text-sm font-semibold">+{{ number_format($pos['pnl_percent'], 2) }}%</div>
                        <div class="text-xs text-emerald-600">+{{ number_format($pos['pnl'], 2, ',', ' ') }} €</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="card">
            <h3 class="font-semibold text-white mb-3">📉 Pires performances</h3>
            <div class="space-y-2">
                @foreach($worstPerformers as $pos)
                <div class="flex justify-between items-center">
                    <div>
                        <span class="font-semibold text-white text-sm">{{ $pos['symbol'] }}</span>
                        <span class="text-slate-500 text-xs ml-2">{{ Str::limit($pos['name'], 20) }}</span>
                    </div>
                    <div class="text-right">
                        <div class="text-red-400 text-sm font-semibold">{{ number_format($pos['pnl_percent'], 2) }}%</div>
                        <div class="text-xs text-red-600">{{ number_format($pos['pnl'], 2, ',', ' ') }} €</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Allocation chart -->
    @if(!empty($allocation))
    <div class="card">
        <h3 class="font-semibold text-white mb-4">Répartition du portefeuille</h3>
        <canvas id="portfolioAlloc" height="60"></canvas>
        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-2">
            @foreach($allocation as $item)
            <div class="flex items-center gap-2 text-xs">
                <div class="w-3 h-3 rounded-full flex-shrink-0"></div>
                <span class="text-slate-300">{{ $item['symbol'] }}</span>
                <span class="text-slate-500 ml-auto">{{ $item['percent'] }}%</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @else
        <div class="card text-center py-12">
            <div class="text-4xl mb-3">📊</div>
            <p class="text-slate-400">Aucun portefeuille. Créez-en un pour commencer.</p>
        </div>
    @endif

    <script>
    document.addEventListener('livewire:init', function() {
        const ctx = document.getElementById('portfolioAlloc');
        if (!ctx) return;
        const alloc = @json($allocation);
        const colors = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4','#84cc16','#f97316'];
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: alloc.map(a => a.symbol),
                datasets: [{ label: 'Valeur (€)', data: alloc.map(a => a.value), backgroundColor: colors, borderRadius: 4 }]
            },
            options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { ticks: { color: '#64748b' }, grid: { color: '#1e293b' } }, y: { ticks: { color: '#94a3b8' }, grid: { display: false } } } }
        });
    });
    </script>
</div>
