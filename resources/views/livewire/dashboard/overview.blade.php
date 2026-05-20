<div class="space-y-6">
    <!-- KPIs top row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card col-span-2 lg:col-span-1 bg-gradient-to-br from-blue-900/40 to-blue-800/20 border-blue-700/50">
            <div class="text-xs text-blue-300 mb-1">Patrimoine total</div>
            <div class="text-2xl font-bold text-white">{{ number_format($netWorth, 0, ',', ' ') }} €</div>
            <div class="text-xs text-slate-400 mt-1">Banque + Portefeuille</div>
        </div>
        <div class="card">
            <div class="text-xs text-slate-400 mb-1">Comptes bancaires</div>
            <div class="text-xl font-bold text-blue-400">{{ number_format($bankBalance, 0, ',', ' ') }} €</div>
        </div>
        <div class="card">
            <div class="text-xs text-slate-400 mb-1">Portefeuille actions</div>
            <div class="text-xl font-bold text-violet-400">{{ number_format($portfolioValue, 0, ',', ' ') }} €</div>
            <div class="text-xs {{ $portfolioPnl >= 0 ? 'text-emerald-400' : 'text-red-400' }} mt-1">
                {{ $portfolioPnl >= 0 ? '+' : '' }}{{ number_format($portfolioPnl, 0, ',', ' ') }} € latent
            </div>
        </div>
        <div class="card">
            <div class="text-xs text-slate-400 mb-1">Variation du jour</div>
            <div class="text-xl font-bold {{ $portfolioDayChange >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $portfolioDayChange >= 0 ? '+' : '' }}{{ number_format($portfolioDayChange, 2, ',', ' ') }} €
            </div>
        </div>
    </div>

    <!-- Budget du mois -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card text-center">
            <div class="text-xs text-slate-400 mb-1">Revenus ce mois</div>
            <div class="text-xl font-bold text-emerald-400">+{{ number_format($monthlyIncome, 0, ',', ' ') }} €</div>
        </div>
        <div class="card text-center">
            <div class="text-xs text-slate-400 mb-1">Dépenses ce mois</div>
            <div class="text-xl font-bold text-red-400">-{{ number_format($monthlyExpenses, 0, ',', ' ') }} €</div>
        </div>
        <div class="card text-center">
            <div class="text-xs text-slate-400 mb-1">Épargne nette</div>
            <div class="text-xl font-bold {{ $monthlySavings >= 0 ? 'text-cyan-400' : 'text-red-400' }}">
                {{ $monthlySavings >= 0 ? '+' : '' }}{{ number_format($monthlySavings, 0, ',', ' ') }} €
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Dernières transactions -->
        <div class="card">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-white">Dernières transactions</h3>
                <a href="{{ route('budget.transactions') }}" class="text-xs text-blue-400 hover:underline">Voir tout →</a>
            </div>
            <div class="space-y-2">
                @forelse($recentTransactions as $txn)
                <div class="flex items-center justify-between py-2 border-b border-dark-700/50 last:border-0">
                    <div class="flex items-center gap-2">
                        @if($txn->category)
                            <span class="text-lg">{{ $txn->category->icon }}</span>
                        @else
                            <span class="w-7 h-7 rounded-full bg-dark-600 flex items-center justify-center text-xs">💸</span>
                        @endif
                        <div>
                            <div class="text-sm text-white">{{ Str::limit($txn->description, 28) }}</div>
                            <div class="text-xs text-slate-500">{{ $txn->date->format('d/m/Y') }} · {{ $txn->account->name }}</div>
                        </div>
                    </div>
                    <div class="text-sm font-semibold {{ $txn->type === 'income' ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $txn->type === 'income' ? '+' : '-' }}{{ number_format($txn->amount, 2, ',', ' ') }} €
                    </div>
                </div>
                @empty
                    <p class="text-slate-500 text-sm text-center py-4">Aucune transaction</p>
                @endforelse
            </div>
        </div>

        <!-- Top performances portefeuille -->
        <div class="card">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-white">Top positions</h3>
                <a href="{{ route('portfolio.positions') }}" class="text-xs text-blue-400 hover:underline">Portefeuille →</a>
            </div>
            @if(empty($topPerformers))
                <p class="text-slate-500 text-sm text-center py-4">Aucune position ouverte</p>
            @else
            <div class="space-y-2">
                @foreach($topPerformers as $pos)
                <div class="flex items-center justify-between py-2 border-b border-dark-700/50 last:border-0">
                    <div>
                        <div class="font-semibold text-white text-sm">{{ $pos['symbol'] }}</div>
                        <div class="text-xs text-slate-500">{{ Str::limit($pos['name'], 25) }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold {{ $pos['pnl'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $pos['pnl'] >= 0 ? '+' : '' }}{{ number_format($pos['pnl'], 2, ',', ' ') }} €
                        </div>
                        <div class="text-xs {{ $pos['pnl_percent'] >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                            {{ $pos['pnl_percent'] >= 0 ? '+' : '' }}{{ number_format($pos['pnl_percent'], 2) }}%
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- Graphique budget mensuel + Allocation -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <h3 class="font-semibold text-white mb-4">Budget 6 mois</h3>
            <canvas id="dashBudgetChart" height="120"></canvas>
        </div>
        <div class="card">
            <h3 class="font-semibold text-white mb-4">Allocation portefeuille</h3>
            @if(empty($allocation))
                <p class="text-slate-500 text-sm text-center py-8">Aucune position</p>
            @else
            <div class="flex gap-4">
                <canvas id="allocationChart" class="max-h-48"></canvas>
                <div class="flex flex-col justify-center gap-1.5 flex-1">
                    @foreach(array_slice($allocation, 0, 6) as $item)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-white">{{ $item['symbol'] }}</span>
                        <span class="text-slate-400">{{ $item['percent'] }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Alertes budget dépassées -->
    @php $overBudget = collect($budgetAdherence)->where('over_budget', true) @endphp
    @if($overBudget->isNotEmpty())
    <div class="card border-red-500/40 bg-red-900/10">
        <h3 class="font-semibold text-red-400 mb-3">⚠️ Budgets dépassés</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($overBudget as $item)
            <div class="bg-dark-800 rounded-xl p-3">
                <div class="text-sm">{{ $item['icon'] }} {{ $item['category'] }}</div>
                <div class="text-red-400 font-semibold text-sm mt-1">
                    {{ number_format($item['spent'], 0, ',', ' ') }} / {{ number_format($item['budget'], 0, ',', ' ') }} €
                </div>
                <div class="text-xs text-red-500">+{{ number_format($item['spent'] - $item['budget'], 0, ',', ' ') }} € dépassé</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <script>
    document.addEventListener('livewire:init', function() {
        // Budget chart
        const budgetCtx = document.getElementById('dashBudgetChart');
        if (budgetCtx) {
            const data = @json($monthlyChart);
            new Chart(budgetCtx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.month),
                    datasets: [
                        { label: 'Revenus', data: data.map(d => d.income), backgroundColor: 'rgba(16,185,129,0.7)', borderRadius: 4 },
                        { label: 'Dépenses', data: data.map(d => d.expenses), backgroundColor: 'rgba(239,68,68,0.7)', borderRadius: 4 },
                    ]
                },
                options: { responsive: true, plugins: { legend: { labels: { color: '#94a3b8', font: { size: 11 } } } }, scales: { x: { ticks: { color: '#64748b' }, grid: { color: '#1e293b' } }, y: { ticks: { color: '#64748b' }, grid: { color: '#1e293b' } } } }
            });
        }

        // Allocation donut
        const allocCtx = document.getElementById('allocationChart');
        if (allocCtx) {
            const alloc = @json($allocation);
            if (alloc.length > 0) {
                new Chart(allocCtx, {
                    type: 'doughnut',
                    data: {
                        labels: alloc.map(a => a.symbol),
                        datasets: [{ data: alloc.map(a => a.value), backgroundColor: ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4'], borderWidth: 0 }]
                    },
                    options: { plugins: { legend: { display: false } }, cutout: '65%' }
                });
            }
        }
    });
    </script>
</div>
