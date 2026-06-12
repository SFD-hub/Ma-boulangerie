@extends('layouts.app')

@section('title', 'Éditer un paiement')

@section('header')
    <h1>Éditer le paiement</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('paiements-factures.update', $paiementFacture) }}">
        @csrf
        @method('PUT')
        @include('paiements-factures._form', ['paiementFacture' => $paiementFacture, 'factures' => $factures])
    </form>
@endsection
