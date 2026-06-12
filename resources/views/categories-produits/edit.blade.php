@extends('layouts.app')

@section('title', 'Éditer une catégorie de produit')

@section('header')
    <h1>Éditer la catégorie "{{ $categorieProduit->nom }}"</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('categories-produits.update', $categorieProduit) }}">
        @csrf
        @method('PUT')
        @include('categories-produits._form', ['categorieProduit' => $categorieProduit])
    </form>
@endsection
