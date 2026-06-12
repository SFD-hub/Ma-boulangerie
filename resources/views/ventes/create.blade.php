@extends('layouts.app')

@section('title', 'Nouvelle vente')

@section('header')
    <h1>Nouvelle vente directe</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('ventes.store') }}">
        @csrf
        @include('ventes._form', ['produits' => $produits])
    </form>
@endsection
