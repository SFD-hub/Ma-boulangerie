@extends('layouts.app')

@section('title', 'Ajouter une boulangerie')

@section('content')

    <a href="{{ route('dashboard') }}" class="back-link">
        ← Retour
    </a>

    <div class="page-title mb-12">Ajouter une boulangerie</div>

    <div class="card">
        <div class="card-subtitle mb-12">
            Cette nouvelle boulangerie sera ajoutée à votre compte. Vous pourrez basculer entre vos boulangeries depuis la barre latérale.
        </div>

        <form method="POST" action="{{ route('boulangeries.ajouter.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="nom">Nom de la boulangerie *</label>
                <input class="form-input" type="text" id="nom" name="nom"
                       value="{{ old('nom') }}" placeholder="Boulangerie Al Amine" required autofocus>
                @error('nom')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="telephone">Téléphone *</label>
                <input class="form-input" type="tel" id="telephone" name="telephone"
                       value="{{ old('telephone') }}" placeholder="77 000 00 00" required>
                @error('telephone')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="adresse">Adresse *</label>
                <input class="form-input" type="text" id="adresse" name="adresse"
                       value="{{ old('adresse') }}" placeholder="Dakar, Médina" required>
                @error('adresse')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email (optionnel)</label>
                <input class="form-input" type="email" id="email" name="email"
                       value="{{ old('email') }}" placeholder="contact@maboulangerie.sn">
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <button class="btn btn-primary btn-full" type="submit">Créer cette boulangerie</button>
        </form>
    </div>

@endsection
