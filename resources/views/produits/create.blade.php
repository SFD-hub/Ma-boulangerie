@extends('layouts.app')

@section('title', 'Créer un produit')

@section('header')
    <h1>Créer un produit</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('produits.store') }}">
        @csrf
        @include('produits._form')
    </form>
@endsection
