@extends('layouts.app')
@section('title', 'Historique')
@section('header', 'Historique des ordres')
@section('subheader', 'Tous vos achats et ventes enregistrés')
@section('actions')
    <livewire:portfolio.trade-form />
@endsection
@section('content')
<div class="card">
    @php $trades = auth()->user()->trades()->with('portfolio')->orderByDesc('executed_at')->paginate(30) @endphp
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="border-b border-dark-700">
                <tr>
                    <th class="table-th">Date</th>
                    <th class="table-th">Symbole</th>
                    <th class="table-th">Type</th>
                    <th class="table-th text-right">Quantité</th>
                    <th class="table-th text-right">Prix</th>
                    <th class="table-th text-right">Frais</th>
                    <th class="table-th text-right">Total</th>
                    <th class="table-th">Portefeuille</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-700/50">
                @forelse($trades as $trade)
                <tr class="hover:bg-dark-700/30 transition-colors">
                    <td class="table-td text-slate-400">{{ $trade->executed_at->format('d/m/Y H:i') }}</td>
                    <td class="table-td font-bold text-white">{{ $trade->symbol }}</td>
                    <td class="table-td">
                        <span class="badge-{{ $trade->type === 'buy' ? 'green' : 'red' }}">
                            {{ $trade->type === 'buy' ? '📈 Achat' : '📉 Vente' }}
                        </span>
                    </td>
                    <td class="table-td text-right text-slate-300">{{ number_format($trade->quantity, 4) }}</td>
                    <td class="table-td text-right text-slate-300">{{ number_format($trade->price, 2, ',', ' ') }}</td>
                    <td class="table-td text-right text-slate-500">{{ number_format($trade->fees, 2, ',', ' ') }}</td>
                    <td class="table-td text-right font-semibold {{ $trade->type === 'buy' ? 'text-red-400' : 'text-emerald-400' }}">
                        {{ $trade->type === 'buy' ? '-' : '+' }}{{ number_format(abs($trade->quantity * $trade->price + ($trade->type === 'buy' ? $trade->fees : -$trade->fees)), 2, ',', ' ') }} €
                    </td>
                    <td class="table-td text-slate-400 text-sm">{{ $trade->portfolio->name ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-12 text-slate-500">Aucun ordre enregistré</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $trades->links() }}</div>
</div>
@endsection
