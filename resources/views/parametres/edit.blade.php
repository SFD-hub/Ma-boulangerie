@extends('layouts.app')

@section('title', 'Paramètres')

@section('content')

    <h1 class="section-title" style="margin-bottom:16px">Paramètres</h1>

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-title">Informations de la boulangerie</div>

        <form method="POST" action="{{ route('parametres.update') }}">
            @csrf @method('PUT')

            <div class="form-group">
                <label class="form-label">Nom de la boulangerie *</label>
                <input class="form-input" type="text" name="nom"
                       value="{{ old('nom', $boulangerie->nom) }}"
                       placeholder="Ma Boulangerie" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Téléphone</label>
                <input class="form-input" type="text" name="telephone"
                       value="{{ old('telephone', $boulangerie->telephone) }}"
                       placeholder="77 000 00 00">
            </div>

            <div class="form-group">
                <label class="form-label">Adresse</label>
                <input class="form-input" type="text" name="adresse"
                       value="{{ old('adresse', $boulangerie->adresse) }}"
                       placeholder="Dakar, Sénégal">
            </div>

<button type="submit" class="btn btn-primary btn-full">Enregistrer</button>
        </form>
    </div>

    {{-- Compte utilisateur --}}
    <div class="card">
        <div class="card-title">Mon compte</div>
        <div class="detail-row">
            <span class="detail-row-label">Nom</span>
            <span class="detail-row-value">{{ auth()->user()->name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Email</span>
            <span class="detail-row-value">{{ auth()->user()->email ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Téléphone</span>
            <span class="detail-row-value">{{ auth()->user()->telephone ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Rôle</span>
            <span class="detail-row-value">{{ ucfirst(auth()->user()->role?->nom ?? '—') }}</span>
        </div>
    </div>

@endsection
