@extends('layouts.app')
@section('title', 'Rapports')
@section('header', 'Rapports financiers')
@section('subheader', 'Analyses et tendances de vos finances personnelles')
@section('content')
<div class="space-y-6">
    <livewire:budget.budget-overview />
</div>
@endsection
