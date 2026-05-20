@extends('layouts.app')
@section('content')
<div class="flex items-center justify-center min-h-64">
    <div class="text-center">
        <div class="text-6xl font-bold text-dark-700 mb-4">500</div>
        <h2 class="text-xl font-bold text-white mb-2">Erreur serveur</h2>
        <p class="text-slate-400 mb-6">Une erreur inattendue s'est produite.</p>
        <a href="{{ route('dashboard') }}" class="btn-primary">← Retour au dashboard</a>
    </div>
</div>
@endsection
