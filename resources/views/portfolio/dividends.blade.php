@extends('layouts.app')
@section('title', 'Dividendes')
@section('header', 'Revenus de dividendes')
@section('subheader', 'Suivi de vos dividendes canadiens — admissibles, ordinaires, DRIP')
@section('content')
    <livewire:portfolio.dividend-tracker />
@endsection
