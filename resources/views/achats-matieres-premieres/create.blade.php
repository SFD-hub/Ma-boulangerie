@extends('layouts.app')

@section('title', 'Ajouter un achat')

@section('content')

    @php($produitOrigine = request('matiere') ? $matieresPremières->firstWhere('id', (int) request('matiere')) : null)
    @if($produitOrigine)
        <a href="{{ route('matieres-premieres.show', $produitOrigine) }}" class="back-link">← {{ $produitOrigine->nom }}</a>
    @else
        <a href="{{ route('achats-matieres-premieres.index') }}" class="back-link">← Achats</a>
    @endif

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-title">Ajouter un achat</div>

        <form method="POST" action="{{ route('achats-matieres-premieres.store') }}">
            @csrf
            {{-- Ouvert depuis la page d'un produit : on y retourne après l'enregistrement --}}
            @if(old('depuis_produit', request('matiere') ? '1' : ''))
                <input type="hidden" name="depuis_produit" value="1">
            @endif

            <div class="form-group">
                <label class="form-label">Produit *</label>
                <select class="form-input" name="matiere_premiere_id" id="produitSelect" required onchange="updateUnit()">
                    <option value="">Sélectionner un produit</option>
                    @foreach($matieresPremières as $mp)
                        <option value="{{ $mp->id }}"
                                data-unite="{{ $mp->unite }}" data-entier="{{ $mp->retrait_manuel ? 1 : 0 }}"
                                {{ old('matiere_premiere_id', request('matiere')) == $mp->id ? 'selected' : '' }}>
                            {{ $mp->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Quantité * </label>
                    <input class="form-input" type="number" name="quantite" id="quantiteInput" min="0.5" step="0.5"
                           value="{{ old('quantite') }}" placeholder="Ex: 5.5" required>
                </div>
                <div class="form-group">
                    <label class="form-label" >Prix total (FCFA) *</label>
                    <input class="form-input" type="number" name="montant" min="1"
                           value="{{ old('montant') }}" placeholder="Ex: 500000" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" >Date d'achat *</label>
                <input class="form-input" type="date" name="date_achat"
                       value="{{ old('date_achat', date('Y-m-d')) }}"
                       max="{{ date('Y-m-d') }}" required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Enregistrer</button>
        </form>
    </div>

    <script>
        function updateUnit() {
            const sel = document.getElementById('produitSelect');
            const opt = sel.options[sel.selectedIndex];
            const unite = opt.getAttribute('data-unite') || '';
            const entier = opt.getAttribute('data-entier') === '1';
            const champ = document.getElementById('quantiteInput');
            // Sel, huile, gasoil : sacs/bidons entiers ; farine, levure, produit : demis acceptés.
            champ.min = entier ? '1' : '0.5';
            champ.step = entier ? '1' : '0.5';
            champ.placeholder = entier ? 'Ex: 2' : 'Ex: 5.5';
        }
        updateUnit();
    </script>

@endsection
