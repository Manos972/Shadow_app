@extends('layouts.app')
@section('title', 'Importer des relevés')
@section('header', 'Importer des relevés')
@section('subheader', 'Importez vos relevés bancaires et de courtage automatiquement')
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <livewire:import.import-wizard />
    </div>
    <div class="space-y-4">
        <div class="glass-card">
            <h3 class="font-semibold text-white mb-3">📋 Formats supportés</h3>
            <div class="space-y-2 text-sm">
                @foreach(['RBC Direct', 'TD WebBroker', 'BMO Ligne d\'action', 'Scotiabank iTRADE', 'Desjardins Disnat', 'Questrade', 'Wealthsimple Trade', 'IBKR (CSV)', 'OFX / QFX', 'CSV générique'] as $f)
                <div class="flex items-center gap-2 text-slate-400">
                    <span class="text-emerald-400">✓</span> {{ $f }}
                </div>
                @endforeach
            </div>
        </div>
        <div class="glass-card">
            <h3 class="font-semibold text-white mb-3">🤖 Catégorisation auto</h3>
            <p class="text-slate-400 text-sm">Les transactions sont automatiquement catégorisées grâce à la reconnaissance des marchands canadiens (Tim Hortons, Metro, Bell, Hydro-Québec, etc.)</p>
        </div>
        <div class="glass-card">
            <h3 class="font-semibold text-white mb-3">🔒 Sécurité</h3>
            <p class="text-slate-400 text-sm">Vos fichiers ne sont jamais conservés sur nos serveurs. L'import se fait directement dans votre espace local.</p>
        </div>
    </div>
</div>
@endsection
