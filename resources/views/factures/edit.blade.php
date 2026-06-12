@extends('layouts.app')

@section('title', 'Modifier facture')

@section('content')

    <a href="{{ route('factures.show', $facture) }}" class="back-link">← Facture</a>

    <div class="card">
        <div class="card-title">Modifier facture {{ $facture->mois }}/{{ $facture->annee }}</div>
        <form method="POST" action="{{ route('factures.update', $facture) }}">
            @csrf
            @method('PUT')
            @include('factures._form', ['facture' => $facture, 'clients' => $clients])
        </form>
    </div>

@endsection
