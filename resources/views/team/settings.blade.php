@extends('layouts.app')
@section('title', 'Mon espace')
@section('header', 'Mon espace financier')
@section('subheader', 'Gérez les membres et paramètres de votre espace partagé')
@section('content')
    <livewire:team.team-settings />
@endsection
