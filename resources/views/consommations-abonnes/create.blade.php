@extends('layouts.app')

@section('title', 'Créer une consommation')

@section('header')
    <h1>Créer une consommation</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('consommations-abonnes.store') }}">
        @csrf
        @include('consommations-abonnes._form', ['clients' => $clients])
    </form>
@endsection
