<div>
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-900/50 border border-green-500/50 p-3 text-sm text-green-300">{{ session('success') }}</div>
    @endif

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <div class="col-span-2">
                <input type="text" wire:model.live.debounce.300ms="search" class="input" placeholder="🔍 Rechercher...">
            </div>
            <select wire:model.live="type" class="input">
                <option value="">Tous types</option>
                <option value="income">Revenus</option>
                <option value="expense">Dépenses</option>
                <option value="transfer">Virements</option>
            </select>
            <select wire:model.live="account" class="input">
                <option value="">Tous comptes</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="dateFrom" class="input" placeholder="Début">
            <input type="date" wire:model.live="dateTo" class="input" placeholder="Fin">
        </div>
    </div>

    <!-- Résumé -->
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="card text-center">
            <div class="text-xs text-slate-400 mb-1">Revenus</div>
            <div class="text-lg font-bold text-emerald-400">+{{ number_format($summary['income'], 2, ',', ' ') }} €</div>
        </div>
        <div class="card text-center">
            <div class="text-xs text-slate-400 mb-1">Dépenses</div>
            <div class="text-lg font-bold text-red-400">-{{ number_format($summary['expense'], 2, ',', ' ') }} €</div>
        </div>
        <div class="card text-center">
            <div class="text-xs text-slate-400 mb-1">Solde</div>
            @php $balance = $summary['income'] - $summary['expense'] @endphp
            <div class="text-lg font-bold {{ $balance >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $balance >= 0 ? '+' : '' }}{{ number_format($balance, 2, ',', ' ') }} €
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-dark-700">
                    <tr>
                        <th class="table-th cursor-pointer" wire:click="sort('date')">
                            Date @if($sortBy === 'date') {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif
                        </th>
                        <th class="table-th">Description</th>
                        <th class="table-th">Catégorie</th>
                        <th class="table-th">Compte</th>
                        <th class="table-th cursor-pointer text-right" wire:click="sort('amount')">
                            Montant @if($sortBy === 'amount') {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif
                        </th>
                        <th class="table-th"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse($transactions as $txn)
                    <tr class="hover:bg-dark-700/30 transition-colors">
                        <td class="table-td text-slate-400">{{ $txn->date->format('d/m/Y') }}</td>
                        <td class="table-td text-white">{{ $txn->description }}</td>
                        <td class="table-td">
                            @if($txn->category)
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full" style="background: {{ $txn->category->color }}22; color: {{ $txn->category->color }}">
                                    {{ $txn->category->icon }} {{ $txn->category->name }}
                                </span>
                            @else
                                <span class="text-slate-500 text-xs">—</span>
                            @endif
                        </td>
                        <td class="table-td text-slate-400 text-sm">{{ $txn->account->name }}</td>
                        <td class="table-td text-right font-semibold {{ $txn->type === 'income' ? 'text-emerald-400' : ($txn->type === 'expense' ? 'text-red-400' : 'text-blue-400') }}">
                            {{ $txn->type === 'income' ? '+' : ($txn->type === 'expense' ? '-' : '↔') }}{{ number_format($txn->amount, 2, ',', ' ') }} €
                        </td>
                        <td class="table-td">
                            <button wire:click="delete({{ $txn->id }})" wire:confirm="Supprimer cette transaction ?" class="text-slate-500 hover:text-red-400 transition-colors text-sm">🗑️</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-12 text-slate-500">Aucune transaction trouvée</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-dark-700">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
