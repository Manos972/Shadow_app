@extends('layouts.app')
@section('title', 'Watchlist')
@section('header', 'Liste de surveillance')
@section('subheader', 'Suivez les titres qui vous intéressent avec objectifs de prix')
@section('content')
    <livewire:portfolio.watchlist-manager />
@endsection
