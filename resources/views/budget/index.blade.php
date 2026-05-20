@extends('layouts.app')
@section('title', 'Budget')
@section('header', 'Budget')
@section('subheader', 'Suivi budgétaire du mois en cours')
@section('content')
    <livewire:budget.budget-overview />
@endsection
