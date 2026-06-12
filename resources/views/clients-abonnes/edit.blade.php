@extends('layouts.app')

@section('title', 'Modifier abonné')

@section('content')

    <a href="{{ route('clients-abonnes.show', $clientAbonne) }}" class="back-link">
        ← {{ $clientAbonne->prenom }} {{ $clientAbonne->nom }}
    </a>

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-title">Modifier l'abonné</div>

        <form method="POST" action="{{ route('clients-abonnes.update', $clientAbonne) }}">
            @csrf @method('PUT')

            <div class="form-group">
                <label class="form-label">Prénom *</label>
                <input class="form-input" type="text" name="prenom"
                       value="{{ old('prenom', $clientAbonne->prenom) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Nom *</label>
                <input class="form-input" type="text" name="nom"
                       value="{{ old('nom', $clientAbonne->nom) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Téléphone *</label>
                <input class="form-input" type="tel" name="telephone"
                       value="{{ old('telephone', $clientAbonne->telephone) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Adresse</label>
                <input class="form-input" type="text" name="adresse"
                       value="{{ old('adresse', $clientAbonne->adresse) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Statut</label>
                <div style="display:flex;gap:20px;padding-top:4px">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
                        <input type="radio" name="actif" value="1" {{ $clientAbonne->actif ? 'checked' : '' }}>
                        <span>Actif</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
                        <input type="radio" name="actif" value="0" {{ !$clientAbonne->actif ? 'checked' : '' }}>
                        <span>Inactif</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Enregistrer</button>
        </form>
    </div>

@endsection
