@extends('layouts.app')

@section('title', 'Éditer un produit')

@section('header')
    <h1>Éditer "{{ $produit->nom }}"</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('produits.update', $produit) }}">
        @csrf
        @method('PUT')
        @include('produits._form', ['produit' => $produit])
    </form>
@endsection
