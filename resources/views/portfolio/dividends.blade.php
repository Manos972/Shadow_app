@extends('layouts.app')
@section('title', 'Dividendes')
@section('header', 'Revenus de dividendes')
@section('subheader', 'Suivez vos dividendes admissibles et revenus passifs')
@section('content')
    <livewire:portfolio.dividend-tracker />
@endsection
