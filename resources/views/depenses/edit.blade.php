@extends('layouts.app')

@section('title', 'Modifier dépense')

@section('content')

    <a href="{{ route('depenses.index') }}" class="back-link">← Dépenses</a>

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-title">Modifier une dépense</div>

        <form method="POST" action="{{ route('depenses.update', $depense) }}">
            @csrf @method('PUT')

            <div class="form-group">
                <label class="form-label">Type *</label>
                <select class="form-input" name="categorie" required>
                    @foreach($categories as $val => $label)
                        <option value="{{ $val }}" {{ old('categorie', $depense->categorie) === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Motif de la dépense *</label>
                <input class="form-input" type="text" name="libelle"
                       value="{{ old('libelle', $depense->libelle) }}"
                       required minlength="3">
            </div>

            <div class="form-group">
                <label class="form-label">Montant (FCFA) *</label>
                <input class="form-input" type="number" name="montant" min="1"
                       value="{{ old('montant', $depense->montant) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Date *</label>
                <input class="form-input" type="date" name="date_depense"
                       value="{{ old('date_depense', $depense->date_depense->format('Y-m-d')) }}"
                       max="{{ date('Y-m-d') }}" required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Enregistrer</button>
        </form>
    </div>

@endsection
