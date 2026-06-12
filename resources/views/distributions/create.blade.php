@extends('layouts.app')

@section('title', 'Créer une distribution')

@section('header')
    <h1>Créer une distribution</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('distributions.store') }}">
        @csrf
        @include('distributions._form', ['livreurs' => $livreurs, 'produits' => $produits])
    </form>
@endsection
