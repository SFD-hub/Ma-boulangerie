@extends('layouts.app')

@section('title', 'Créer un paiement')

@section('header')
    <h1>Créer un paiement</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('paiements-factures.store') }}">
        @csrf
        @include('paiements-factures._form', ['factures' => $factures])
    </form>
@endsection
