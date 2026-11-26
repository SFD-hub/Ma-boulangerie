@extends('layouts.app')

@section('title', 'Modifier un achat')

@section('content')

    {{-- Ouvert depuis la page d'un produit : on y retourne après l'enregistrement --}}
    @php($depuisProduit = (bool) old('depuis_produit', request('depuis_produit')))
    <a href="{{ $depuisProduit ? route('matieres-premieres.show', $achatMatierePremiere->matiere_premiere_id) : route('achats-matieres-premieres.index') }}" class="back-link">← {{ $depuisProduit ? $achatMatierePremiere->matierePremiere->nom : 'Achats' }}</a>

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-title">{{ $achatMatierePremiere->depart ? "Modifier le stock initial" : "Modifier l'achat du " . $achatMatierePremiere->date_achat->format('d/m/Y') }}</div>

        <form method="POST" action="{{ route('achats-matieres-premieres.update', $achatMatierePremiere) }}">
            @csrf @method('PUT')
            @if($depuisProduit)
                <input type="hidden" name="depuis_produit" value="1">
            @endif
            {{-- Le produit d'un achat ne change pas : on corrige la quantité, le prix ou la date. --}}
            <input type="hidden" name="matiere_premiere_id" value="{{ $achatMatierePremiere->matiere_premiere_id }}">

            <div class="form-group">
                <label class="form-label">Produit</label>
                <select class="form-input" id="produitSelect" disabled onchange="updateUnit()">
                    <option value="">Sélectionner un produit</option>
                    @foreach($matieresPremières as $mp)
                        <option value="{{ $mp->id }}"
                                data-unite="{{ $mp->unite }}" data-entier="{{ $mp->retrait_manuel ? 1 : 0 }}"
                                {{ old('matiere_premiere_id', $achatMatierePremiere->matiere_premiere_id) == $mp->id ? 'selected' : '' }}>
                            {{ $mp->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Quantité * </label>
                    <input class="form-input" type="number" name="quantite" id="quantiteInput" min="0.5" step="0.5"
                           value="{{ old('quantite', $achatMatierePremiere->quantite) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ $achatMatierePremiere->depart ? 'Valeur du stock (FCFA)' : 'Prix total (FCFA)' }} *</label>
                    <input class="form-input" type="number" name="montant" min="1"
                           value="{{ old('montant', $achatMatierePremiere->montant) }}" required>
                    <div style="font-size:12px;color:#9CA3AF;margin-top:6px">Le coût des productions déjà faites ne change pas.</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ $achatMatierePremiere->depart ? "Date" : "Date d'achat" }} *</label>
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
            const unite = opt ? (opt.getAttribute('data-unite') || '') : '';
            const entier = opt ? opt.getAttribute('data-entier') === '1' : false;
            const champ = document.getElementById('quantiteInput');
            champ.min = entier ? '1' : '0.5';
            champ.step = entier ? '1' : '0.5';
        }
        updateUnit();
    </script>

@endsection
