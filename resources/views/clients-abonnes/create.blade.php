@extends('layouts.app')

@section('title', 'Ajouter un abonné')

@section('content')

    <a href="{{ route('clients-abonnes.index') }}" class="back-link">← Abonnés</a>

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-title">Ajouter un abonné</div>

        <form method="POST" action="{{ route('clients-abonnes.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Prénom *</label>
                <input class="form-input" type="text" name="prenom"
                       value="{{ old('prenom') }}" placeholder="Ex : Aminata" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Nom *</label>
                <input class="form-input" type="text" name="nom"
                       value="{{ old('nom') }}" placeholder="Ex : Diallo" required>
            </div>

            <div class="form-group">
                <label class="form-label">Téléphone *</label>
                <input class="form-input" type="tel" name="telephone"
                       value="{{ old('telephone') }}" placeholder="77 000 00 00" required>
            </div>

            <div class="form-group">
                <label class="form-label">Adresse</label>
                <input class="form-input" type="text" name="adresse"
                       value="{{ old('adresse') }}" placeholder="Quartier, ville">
            </div>

            <button type="submit" class="btn btn-primary btn-full">Ajouter l'abonné</button>
        </form>
    </div>

@endsection
