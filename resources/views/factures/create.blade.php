@extends('layouts.app')

@section('title', 'Créer une facture')

@section('header')
    <h1>Créer une facture</h1>
@endsection

@section('content')
    @if (session('error'))
        <div style="background: #fee; border: 1px solid #c33; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; color: #c33;">
            {{ session('error') }}
        </div>
    @endif

    <script>
        const CONSOMMATIONS = @json($consommationsData);
    </script>

    <form method="POST" action="{{ route('factures.store') }}">
        @csrf
        @include('factures._form', ['clients' => $clients, 'modeCreation' => true])
    </form>
@endsection
