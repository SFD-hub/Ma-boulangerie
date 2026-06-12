@extends('layouts.app')

@section('title', 'Modifier le livreur')

@section('content')

    <a href="{{ route('livreurs.show', $livreur) }}" class="back-link">
        ← {{ $livreur->prenom }} {{ $livreur->nom }}
    </a>

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-title">Modifier le livreur</div>

        <form method="POST" action="{{ route('livreurs.update', $livreur) }}">
            @csrf @method('PUT')

            <div class="form-group">
                <label class="form-label">Prénom *</label>
                <input class="form-input" type="text" name="prenom"
                       value="{{ old('prenom', $livreur->prenom) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Nom *</label>
                <input class="form-input" type="text" name="nom"
                       value="{{ old('nom', $livreur->nom) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Téléphone *</label>
                <input class="form-input" type="text" name="telephone"
                       value="{{ old('telephone', $livreur->telephone) }}" required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Enregistrer</button>
        </form>
    </div>

@endsection
