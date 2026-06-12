@extends('layouts.app')

@section('title', 'Modifier versement')

@section('content')

    <a href="{{ url()->previous() }}" class="back-link">← Retour</a>

    <div class="card">
        <div class="card-title">Versement du {{ $versement->date_versement->format('d/m/Y') }}</div>

        @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom:12px">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('versements.update', $versement) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Livreur *</label>
                <select class="form-input" name="livreur_id" required>
                    <option value="">-- Sélectionner --</option>
                    @foreach($livreurs as $livreur)
                        <option value="{{ $livreur->id }}"
                            {{ old('livreur_id', $versement->livreur_id) == $livreur->id ? 'selected' : '' }}>
                            {{ $livreur->prenom }} {{ $livreur->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Date de versement *</label>
                <input class="form-input" type="date" name="date_versement"
                       value="{{ old('date_versement', $versement->date_versement->format('Y-m-d')) }}"
                       max="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Montant versé (FCFA) *</label>
                <input class="form-input" type="number" name="montant_verse" step="1" min="1"
                       value="{{ old('montant_verse', $versement->montant_verse) }}" required>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>

@endsection
