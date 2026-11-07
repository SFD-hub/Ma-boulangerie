@extends('layouts.app')

@section('title', 'Paramètres')

@section('content')

    <h1 class="section-title" style="margin-bottom:16px">Paramètres</h1>

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-title">Informations de la boulangerie</div>

        <form method="POST" action="{{ route('parametres.update') }}">
            @csrf @method('PUT')

            <div class="form-group">
                <label class="form-label">Nom de la boulangerie *</label>
                <input class="form-input" type="text" name="nom"
                       value="{{ old('nom', $boulangerie->nom) }}"
                       placeholder="Ma Boulangerie" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Téléphone</label>
                <input class="form-input" type="text" name="telephone"
                       value="{{ old('telephone', $boulangerie->telephone) }}"
                       placeholder="77 000 00 00">
            </div>

            <div class="form-group">
                <label class="form-label">Adresse</label>
                <input class="form-input" type="text" name="adresse"
                       value="{{ old('adresse', $boulangerie->adresse) }}"
                       placeholder="Dakar, Sénégal">
            </div>

            <div style="font-size:13px;font-weight:700;color:#111827;margin:20px 0 4px;padding-top:16px;border-top:1px solid #E5E7EB">Prix de vente Boutique (FCFA)</div>
            <div style="font-size:12px;color:#6B7280;margin-bottom:12px">Le montant d'une vente Boutique est calculé automatiquement avec ces prix.</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px">
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Entier</label>
                    <input class="form-input" type="number" name="prix_boutique_entier" min="1" required
                           value="{{ old('prix_boutique_entier', $boulangerie->prix_boutique_entier ?? 150) }}">
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Moitié</label>
                    <input class="form-input" type="number" name="prix_boutique_moitie" min="1" required
                           value="{{ old('prix_boutique_moitie', $boulangerie->prix_boutique_moitie ?? 75) }}">
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Tiers</label>
                    <input class="form-input" type="number" name="prix_boutique_tiers" min="1" required
                           value="{{ old('prix_boutique_tiers', $boulangerie->prix_boutique_tiers ?? 50) }}">
                </div>
            </div>

<button type="submit" class="btn btn-primary btn-full">Enregistrer</button>
        </form>
    </div>

    {{-- Compte utilisateur --}}
    <div class="card">
        <div class="card-title">Mon compte</div>

        <form method="POST" action="{{ route('profil.update') }}">
            @csrf @method('PUT')

            <div class="form-group">
                <label class="form-label">Nom complet *</label>
                <input class="form-input" type="text" name="name"
                       value="{{ old('name', auth()->user()->name) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Numéro de téléphone *</label>
                <input class="form-input" type="text" name="telephone"
                       value="{{ old('telephone', auth()->user()->telephone) }}" required>
            </div>

            <div class="form-group" style="margin-top:20px;padding-top:16px;border-top:1px solid #E5E7EB">
                <label class="form-label">Mot de passe actuel *</label>
                <div class="password-wrap">
                    <input class="form-input" type="password" id="current_password" name="current_password" placeholder="••••••••" required>
                    <button type="button" class="eye-btn" onclick="togglePwd('current_password')">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nouveau mot de passe</label>
                <div class="password-wrap">
                    <input class="form-input" type="password" id="password" name="password" placeholder="••••••••">
                    <button type="button" class="eye-btn" onclick="togglePwd('password')">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Confirmer le nouveau mot de passe</label>
                <div class="password-wrap">
                    <input class="form-input" type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••">
                    <button type="button" class="eye-btn" onclick="togglePwd('password_confirmation')">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Enregistrer</button>
        </form>
    </div>

@endsection
