@extends('layouts.app')

@section('title', 'Modifier consommation')

@section('content')

    <a href="{{ url()->previous() }}" class="back-link">← Retour</a>

    <div class="card">
        <div class="card-title">Consommation du {{ $consommationAbonne->date_consommation->format('d/m/Y') }}</div>

        @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom:12px">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('consommations-abonnes.update', $consommationAbonne) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Client</label>
                <select class="form-input" name="client_abonne_id" required>
                    <option value="">-- Sélectionner --</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}"
                            {{ old('client_abonne_id', $consommationAbonne->client_abonne_id) == $client->id ? 'selected' : '' }}>
                            {{ $client->prenom }} {{ $client->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Date *</label>
                <input class="form-input" type="date" name="date_consommation"
                       value="{{ old('date_consommation', $consommationAbonne->date_consommation->format('Y-m-d')) }}"
                       max="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Quantité *</label>
                <input class="form-input" type="number" name="quantite" min="1"
                       value="{{ old('quantite', $consommationAbonne->quantite) }}" required>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>

@endsection
