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
    <div class="password-wrap">
        <input class="form-input" type="password" id="password" name="password"
               placeholder="••••••••" {{ isset($gerant) ? '' : 'required' }}>
        <button type="button" class="eye-btn" onclick="togglePwd('password')">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
        </button>
    </div>
</div>

<div class="form-group">
    <label class="form-label">Confirmer le mot de passe {{ isset($gerant) ? '' : '*' }}</label>
    <div class="password-wrap">
        <input class="form-input" type="password" id="password_confirmation" name="password_confirmation"
               placeholder="••••••••" {{ isset($gerant) ? '' : 'required' }}>
        <button type="button" class="eye-btn" onclick="togglePwd('password_confirmation')">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
        </button>
    </div>
</div>

<div style="display:flex;gap:10px;margin-top:8px">
    <button type="submit" class="btn btn-primary">
        {{ isset($gerant) ? 'Mettre à jour' : 'Créer le gérant' }}
    </button>
    <a href="{{ route('gerants.index') }}" class="btn btn-secondary">Annuler</a>
</div>
