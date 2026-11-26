@extends('layouts.app')

@section('title', 'Créer une facture')

@section('header')
    <h1>Créer une facture</h1>
@endsection

@section('content')

    <script>
        const CONSOMMATIONS = @json($consommationsData);
    </script>

    <form method="POST" action="{{ route('factures.store') }}">
        @csrf
        @include('factures._form', ['clients' => $clients, 'modeCreation' => true])
    </form>
@endsection
