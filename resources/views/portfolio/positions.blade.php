@extends('layouts.app')
@section('title', 'Positions')
@section('header', 'Positions ouvertes')
@section('subheader', 'Vos positions actuelles avec P&L en temps réel')
@section('content')
    <livewire:portfolio.position-list />
@endsection
