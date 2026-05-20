@extends('layouts.app')
@section('title', 'Dashboard')
@section('header', 'Tableau de bord')
@section('subheader', 'Vue globale de votre patrimoine et finances')
@section('content')
    <livewire:dashboard.overview />
@endsection
