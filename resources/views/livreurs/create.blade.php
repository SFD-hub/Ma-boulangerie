@extends('layouts.app')

@section('title', 'Ajouter un livreur')

@section('content')

    <a href="{{ route('livreurs.index') }}" class="back-link">← Livreurs</a>

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-title">Ajouter un livreur</div>

        <form method="POST" action="{{ route('livreurs.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Prénom *</label>
                <input class="form-input" type="text" name="prenom"
                       value="{{ old('prenom') }}" placeholder="Ex : Moussa" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Nom *</label>
                <input class="form-input" type="text" name="nom"
                       value="{{ old('nom') }}" placeholder="Ex : Diallo" required>
            </div>

            <div class="form-group">
                <label class="form-label">Téléphone *</label>
                <input class="form-input" type="text" name="telephone"
                       value="{{ old('telephone') }}" placeholder="77 000 00 00" required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Ajouter le livreur</button>
        </form>
    </div>

@endsection
