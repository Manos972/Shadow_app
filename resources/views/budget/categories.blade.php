@extends('layouts.app')
@section('title', 'Catégories')
@section('header', 'Catégories')
@section('subheader', 'Gérez vos catégories de revenus et dépenses avec budgets mensuels')
@section('content')
    <livewire:budget.category-manager />
@endsection
