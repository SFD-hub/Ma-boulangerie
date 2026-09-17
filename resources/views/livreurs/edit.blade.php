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

            <div class="form-group">
                <label class="form-label">Type *</label>
                <div style="display:flex;gap:20px;padding-top:4px">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
                        <input type="radio" name="type" value="livreur" {{ old('type', $livreur->type) === 'livreur' ? 'checked' : '' }}>
                        <span>Livreur</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
                        <input type="radio" name="type" value="client" {{ old('type', $livreur->type) === 'client' ? 'checked' : '' }}>
                        <span>Client</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Enregistrer</button>
        </form>
    </div>

@endsection
