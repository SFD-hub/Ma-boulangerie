@extends('layouts.app')

@section('title', 'Créer un versement')

@section('header')
    <h1>Créer un versement</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('versements.store') }}">
        @csrf
        @include('versements._form', ['livreurs' => $livreurs])
    </form>
@endsection
