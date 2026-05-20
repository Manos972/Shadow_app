<div>
    <!-- Sélecteur de mois -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-white">Vue budgétaire</h2>
        <input type="month" wire:model.live="selectedMonth" class="input w-auto">
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card">
            <div class="text-sm text-slate-400 mb-1">Revenus du mois</div>
            <div class="text-2xl font-bold text-emerald-400">+{{ number_format($income, 2, ',', ' ') }} €</div>
        </div>
        <div class="card">
            <div class="text-sm text-slate-400 mb-1">Dépenses du mois</div>
            <div class="text-2xl font-bold text-red-400">-{{ number_format($expenses, 2, ',', ' ') }} €</div>
        </div>
        <div class="card">
            <div class="text-sm text-slate-400 mb-1">Épargne nette</div>
            <div class="text-2xl font-bold {{ $savings >= 0 ? 'text-blue-400' : 'text-red-400' }}">
                {{ $savings >= 0 ? '+' : '' }}{{ number_format($savings, 2, ',', ' ') }} €
            </div>
        </div>
    </div>

    <!-- Graphique revenus/dépenses (Chart.js) -->
    <div class="card mb-6">
        <h3 class="text-sm font-semibold text-slate-300 mb-4">Évolution 6 mois</h3>
        <canvas id="budgetChart" height="80"></canvas>
    </div>

    <!-- Adhérence budget par catégorie -->
    <div class="card">
        <h3 class="text-sm font-semibold text-slate-300 mb-4">Budget par catégorie</h3>
        @if(empty($budgetItems))
            <p class="text-slate-500 text-sm">Aucun budget défini. Configurez des budgets dans <a href="{{ route('budget.categories') }}" class="text-blue-400 underline">Catégories</a>.</p>
        @else
        <div class="space-y-3">
            @foreach($budgetItems as $item)
            <div>
                <div class="flex justify-between items-center text-sm mb-1">
                    <span class="flex items-center gap-2">
                        <span>{{ $item['icon'] }}</span>
                        <span class="text-white">{{ $item['category'] }}</span>
                    </span>
                    <span class="{{ $item['over_budget'] ? 'text-red-400' : 'text-slate-400' }}">
                        {{ number_format($item['spent'], 0, ',', ' ') }} / {{ number_format($item['budget'], 0, ',', ' ') }} €
                        @if($item['over_budget']) <span class="text-xs">⚠️ dépassé</span> @endif
                    </span>
                </div>
                <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all" style="width: {{ $item['percent'] }}%; background: {{ $item['over_budget'] ? '#ef4444' : $item['color'] }}"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <script>
    document.addEventListener('livewire:init', () => {
        const ctx = document.getElementById('budgetChart');
        if (!ctx) return;

        const data = @json($monthlyData);
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.month),
                datasets: [
                    { label: 'Revenus', data: data.map(d => d.income), backgroundColor: 'rgba(16, 185, 129, 0.7)', borderRadius: 4 },
                    { label: 'Dépenses', data: data.map(d => d.expenses), backgroundColor: 'rgba(239, 68, 68, 0.7)', borderRadius: 4 },
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#94a3b8' } } },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { color: '#334155' } },
                    y: { ticks: { color: '#94a3b8' }, grid: { color: '#334155' } }
                }
            }
        });
    });
    </script>
</div>
