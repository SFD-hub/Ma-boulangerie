@extends('layouts.app')

@section('title', 'Modifier distribution')

@section('content')

    <a href="{{ url()->previous() }}" class="back-link">← Retour</a>

    <div class="card">
        <div class="card-title">Distribution du {{ $distribution->date_distribution->format('d/m/Y') }}</div>

        <form method="POST" action="{{ route('distributions.update', $distribution) }}">
            @csrf
            @method('PUT')
            @include('distributions._form', [
                'distribution' => $distribution,
                'livreurs'     => $livreurs,
                'produits'     => $produits,
            ])
        </form>
    </div>

@endsection
