@extends('layouts.app')
@section('title', 'Alertes')
@section('header', 'Alertes de prix')
@section('subheader', 'Soyez notifié dès qu\'un seuil est atteint (prix, RSI, variation)')
@section('content')
    <livewire:portfolio.alert-manager />
@endsection
