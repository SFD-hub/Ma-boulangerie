@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom:12px">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="form-group">
    <label class="form-label">Date de production *</label>
    <input class="form-input" type="date" name="date_production"
           value="{{ old('date_production', isset($production) ? $production->date_production->format('Y-m-d') : date('Y-m-d')) }}"
           max="{{ date('Y-m-d') }}" required>
</div>

<div class="form-group">
    <label class="form-label">Sacs de farine * <span style="font-weight:400;font-size:12px;color:#718096">(1 sac = 50 kg)</span></label>
    <input class="form-input" type="number" name="nombre_sacs" min="1"
           value="{{ old('nombre_sacs', isset($production) ? $production->nombre_sacs : '') }}"
           placeholder="ex: 2" required>
</div>

<div class="form-group">
    <label class="form-label">Paquets de levure * <span style="font-weight:400;font-size:12px;color:#718096">(1 paquet = 1 kg)</span></label>
    <input class="form-input" type="number" name="nombre_paquets_levure" min="1"
           value="{{ old('nombre_paquets_levure', isset($production) ? $production->quantite_levure : '') }}"
           placeholder="ex: 1" required>
</div>

<div class="form-group">
    <label class="form-label">Pains produits *</label>
    <input class="form-input" type="number" name="nombre_pains_produits" min="1"
           value="{{ old('nombre_pains_produits', isset($production) ? $production->nombre_pains_produits : '') }}"
           placeholder="ex: 500" required>
</div>

<div style="display:flex;gap:10px;margin-top:8px">
    <button type="submit" class="btn btn-primary">
        {{ isset($production) ? 'Mettre à jour' : 'Enregistrer' }}
    </button>
    <a href="{{ isset($production) ? route('productions.index') : route('productions.index') }}" class="btn btn-secondary">
        Annuler
    </a>
</div>
