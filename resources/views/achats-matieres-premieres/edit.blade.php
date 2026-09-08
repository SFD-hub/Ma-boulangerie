@extends('layouts.app')

@section('title', 'Modifier un achat')

@section('content')

    <a href="{{ route('matieres-premieres.index') }}" class="back-link">← Stock</a>

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-title">Modifier l'achat du {{ $achatMatierePremiere->date_achat->format('d/m/Y') }}</div>

        <form method="POST" action="{{ route('achats-matieres-premieres.update', $achatMatierePremiere) }}">
            @csrf @method('PUT')

            <div class="form-group">
                <label class="form-label">Produit *</label>
                <select class="form-input" name="matiere_premiere_id" id="produitSelect" required onchange="updateUnit()">
                    <option value="">Sélectionner un produit</option>
                    @foreach($matieresPremières as $mp)
                        <option value="{{ $mp->id }}"
                                data-nom="{{ $mp->nom }}"
                                {{ old('matiere_premiere_id', $achatMatierePremiere->matiere_premiere_id) == $mp->id ? 'selected' : '' }}>
                            {{ $mp->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Quantité * <span id="unitLabel" style="color:var(--text2);font-weight:400">(sacs / paquets)</span></label>
                    <input class="form-input" type="number" name="quantite" min="0.5" step="0.5"
                           value="{{ old('quantite', $achatMatierePremiere->quantite) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Prix total (FCFA) *</label>
                    <input class="form-input" type="number" name="montant" min="1"
                           value="{{ old('montant', $achatMatierePremiere->montant) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Date d'achat *</label>
                <input class="form-input" type="date" name="date_achat"
                       value="{{ old('date_achat', $achatMatierePremiere->date_achat->format('Y-m-d')) }}"
                       max="{{ date('Y-m-d') }}" required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Enregistrer</button>
        </form>
    </div>

    <script>
        function updateUnit() {
            const sel = document.getElementById('produitSelect');
            const opt = sel.options[sel.selectedIndex];
            const nom = opt ? (opt.getAttribute('data-nom') || '') : '';
            const label = document.getElementById('unitLabel');
            if (nom === 'Farine') label.textContent = '(sacs)';
            else if (nom === 'Levure') label.textContent = '(paquets)';
            else label.textContent = '(sacs / paquets)';
        }
        updateUnit();
    </script>

@endsection
