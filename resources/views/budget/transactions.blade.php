@extends('layouts.app')
@section('title', 'Transactions')
@section('header', 'Transactions')
@section('subheader', 'Historique complet de vos revenus et dépenses')
@section('actions')
    <livewire:budget.transaction-form />
@endsection
@section('content')
    <livewire:budget.transaction-list />
@endsection
