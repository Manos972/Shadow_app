@extends('layouts.app')
@section('title', 'Analyse')
@section('header', 'Analyse technique')
@section('subheader', 'Indicateurs RSI, MACD, moyennes mobiles')
@section('content')
<div class="space-y-6">
    <livewire:portfolio.stock-search />
    <div class="card">
        <p class="text-slate-400 text-sm">Cherchez un titre ci-dessus pour afficher son analyse technique complète (RSI, MACD, SMA 20/50/200).</p>
    </div>
</div>
@endsection
