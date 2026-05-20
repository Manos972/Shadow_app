<div class="space-y-6">
    @if(session('success'))
        <div class="glass-alert-success">✅ {{ session('success') }}</div>
    @endif

    <!-- KPIs dividendes -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="text-xs text-slate-500 mb-1">Dividendes {{ now()->year }}</div>
            <div class="text-2xl font-bold gradient-text-green">${{ number_format($ytdTotal, 2, ',', ' ') }}</div>
            <div class="text-xs text-slate-500 mt-1">CAD</div>
        </div>
        <div class="stat-card">
            <div class="text-xs text-slate-500 mb-1">Mensuel moyen</div>
            <div class="text-xl font-bold text-white">${{ number_format($ytdTotal / max(now()->month, 1), 2, ',', ' ') }}</div>
        </div>
        <div class="stat-card">
            <div class="text-xs text-slate-500 mb-1">An dernier</div>
            <div class="text-xl font-bold text-slate-300">${{ number_format($lastYearTotal, 2, ',', ' ') }}</div>
        </div>
        <div class="stat-card">
            <div class="text-xs text-slate-500 mb-1">Rendement div.</div>
            <div class="text-xl font-bold text-emerald-400">{{ $dividendYield }}%</div>
        </div>
    </div>

    <!-- Chart mensuel -->
    <div class="glass-card">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-white">Dividendes par mois</h3>
            <button wire:click="openModal()" class="btn-primary text-sm">+ Enregistrer</button>
        </div>
        <canvas id="divChart" height="80"></canvas>
    </div>

    <!-- Tableau -->
    <div class="glass-card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-white/10">
                    <tr>
                        <th class="table-th">Date ex-div.</th>
                        <th class="table-th">Symbole</th>
                        <th class="table-th">Type</th>
                        <th class="table-th text-right">$/action</th>
                        <th class="table-th text-right">Actions</th>
                        <th class="table-th text-right font-bold">Total</th>
                        <th class="table-th text-center">DRIP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($dividends as $div)
                    <tr class="table-row">
                        <td class="table-td text-slate-400">{{ $div->ex_date->format('d M Y') }}</td>
                        <td class="table-td font-bold text-white">{{ $div->symbol }}</td>
                        <td class="table-td"><span class="badge badge-green text-xs">{{ $div->getTypeLabel() }}</span></td>
                        <td class="table-td text-right text-slate-300">${{ $div->amount_per_share }}</td>
                        <td class="table-td text-right text-slate-300">{{ number_format($div->shares, 4) }}</td>
                        <td class="table-td text-right font-bold text-emerald-400">${{ number_format($div->totalAmount(), 2, ',', ' ') }}</td>
                        <td class="table-td text-center">
                            @if($div->is_drip) <span class="badge badge-blue">DRIP</span>
                            @else <span class="text-slate-600">—</span> @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-12 text-slate-500">Aucun dividende. Commencez à enregistrer vos revenus passifs.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" wire:click.self="$set('showModal', false)">
        <div class="glass-card w-full max-w-lg shadow-2xl">
            <h3 class="text-lg font-bold text-white mb-5">Enregistrer un dividende</h3>
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Symbole</label>
                        <input type="text" wire:model="symbol" class="input uppercase" placeholder="RY.TO">
                    </div>
                    <div>
                        <label class="label">Portefeuille</label>
                        <select wire:model="portfolio_id" class="input">
                            <option value="">Aucun</option>
                            @foreach($portfolios as $pf)
                            <option value="{{ $pf->id }}">{{ $pf->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">$/action</label>
                        <input type="number" wire:model="amount_per_share" step="0.0001" class="input" placeholder="0.98">
                    </div>
                    <div>
                        <label class="label">Nombre d'actions</label>
                        <input type="number" wire:model="shares" step="0.0001" class="input" placeholder="25">
                    </div>
                    <div>
                        <label class="label">Date ex-dividende</label>
                        <input type="date" wire:model="ex_date" class="input">
                    </div>
                    <div>
                        <label class="label">Type</label>
                        <select wire:model="type" class="input">
                            <option value="eligible">Admissible (crédit d'impôt)</option>
                            <option value="ordinary">Ordinaire</option>
                            <option value="return_of_capital">Remboursement de capital</option>
                            <option value="capital_gain">Gain en capital</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" wire:model.live="is_drip" id="drip" class="rounded">
                    <label for="drip" class="text-sm text-slate-300 cursor-pointer">DRIP (réinvestissement auto)</label>
                </div>
                @if($is_drip)
                <div>
                    <label class="label">Prix DRIP par action</label>
                    <input type="number" wire:model="drip_price" step="0.0001" class="input" placeholder="Prix d'achat des actions DRIP">
                </div>
                @endif
                @if($amount_per_share && $shares)
                <div class="glass-card-sm text-sm text-center">
                    Total : <span class="font-bold text-emerald-400">${{ number_format((float)$amount_per_share * (float)$shares, 2, ',', ' ') }} CAD</span>
                </div>
                @endif
                <div class="flex gap-3">
                    <button type="submit" class="btn-success flex-1">Enregistrer</button>
                    <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Annuler</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>
    document.addEventListener('livewire:init', function() {
        const ctx = document.getElementById('divChart');
        if (!ctx) return;
        const data = @json($byMonth->toArray());
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(data),
                datasets: [{ label: 'Dividendes (CAD)', data: Object.values(data), backgroundColor: 'rgba(16,185,129,0.7)', borderRadius: 6, borderSkipped: false }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { ticks: { color: '#475569' }, grid: { display: false } }, y: { ticks: { color: '#475569', callback: v => '$' + v }, grid: { color: 'rgba(255,255,255,0.04)' } } }
            }
        });
    });
    </script>
</div>
