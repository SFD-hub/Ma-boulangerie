@extends('layouts.app')

@section('title', 'Ajouter une dépense')

@section('content')

    <a href="{{ route('depenses.index') }}" class="back-link">← Dépenses</a>

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-title">Ajouter une dépense</div>

        <form method="POST" action="{{ route('depenses.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Type *</label>
                <select class="form-input" name="categorie" required>
                    <option value="">Sélectionner...</option>
                    @foreach($categories as $val => $label)
                        <option value="{{ $val }}" {{ old('categorie') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

            </div>

            <div class="form-group">
                <label class="form-label">Motif de la dépense *</label>
                <input class="form-input" type="text" name="libelle"
                       value="{{ old('libelle') }}"
                       placeholder="Ex : Salaire Moussa — juin 2026"
                       required minlength="3">
            </div>

            <div class="form-group">
                <label class="form-label">Montant (FCFA) *</label>
                <input class="form-input" type="number" name="montant" min="1"
                       value="{{ old('montant') }}" placeholder="Ex : 50 000" required>
            </div>

            <div class="form-group">
                <label class="form-label">Date *</label>
                <input class="form-input" type="date" name="date_depense"
                       value="{{ old('date_depense', date('Y-m-d')) }}"
                       max="{{ date('Y-m-d') }}" required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Enregistrer</button>
        </form>
    </div>

@endsection
