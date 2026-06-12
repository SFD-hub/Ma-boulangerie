@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom:12px">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="form-group">
    <label class="form-label">Nom complet *</label>
    <input class="form-input" type="text" name="name"
           value="{{ old('name', isset($gerant) ? $gerant->name : '') }}"
           placeholder="Prénom Nom" required>
</div>

<div class="form-group">
    <label class="form-label">Numéro de téléphone *</label>
    <input class="form-input" type="text" name="telephone"
           value="{{ old('telephone', isset($gerant) ? $gerant->telephone : '') }}"
           placeholder="77 123 45 67" required>
</div>

<div class="form-group">
    <label class="form-label">Mot de passe {{ isset($gerant) ? '(laisser vide = inchangé)' : '*' }}</label>
    <input class="form-input" type="password" name="password"
           placeholder="••••••••" {{ isset($gerant) ? '' : 'required' }}>
</div>

<div class="form-group">
    <label class="form-label">Confirmer le mot de passe {{ isset($gerant) ? '' : '*' }}</label>
    <input class="form-input" type="password" name="password_confirmation"
           placeholder="••••••••" {{ isset($gerant) ? '' : 'required' }}>
</div>

<div style="display:flex;gap:10px;margin-top:8px">
    <button type="submit" class="btn btn-primary">
        {{ isset($gerant) ? 'Mettre à jour' : 'Créer le gérant' }}
    </button>
    <a href="{{ route('gerants.index') }}" class="btn btn-secondary">Annuler</a>
</div>
