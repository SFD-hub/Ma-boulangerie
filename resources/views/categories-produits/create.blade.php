@extends('layouts.app')

@section('title', 'Créer une catégorie de produit')

@section('header')
    <h1>Créer une catégorie de produit</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('categories-produits.store') }}">
        @csrf
        @include('categories-produits._form')
    </form>
@endsection
