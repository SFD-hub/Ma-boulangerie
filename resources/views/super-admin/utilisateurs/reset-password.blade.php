@extends('layouts.app')

@section('title', 'Réinitialiser le mot de passe')

@section('content')

{{-- En-tête --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
    <a href="{{ route('super-admin.utilisateurs') }}"
       style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <span style="font-size:17px;font-weight:700;color:#111827">Réinitialiser le mot de passe</span>
    <div style="width:36px"></div>
</div>

{{-- Carte utilisateur --}}
<div class="card" style="margin-bottom:16px;padding:16px">
    <div style="display:flex;align-items:center;gap:12px">
        <div style="width:48px;height:48px;border-radius:50%;background:var(--orange-soft);
                    display:flex;align-items:center;justify-content:center;
                    font-size:20px;font-weight:700;color:var(--orange-dark);flex-shrink:0">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:#111827">{{ $user->name }}</div>
            @if($user->role)
                <span class="badge {{ $user->role->nom === 'proprietaire' ? 'badge-orange' : 'badge-blue' }}" style="margin-top:4px;display:inline-block">
                    {{ $user->role->nom === 'proprietaire' ? 'Propriétaire' : 'Gérant' }}
                </span>
            @endif
            @if($user->boulangerie)
                <div style="font-size:13px;color:var(--text2);margin-top:4px">
                    🏪 {{ $user->boulangerie->nom }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Avertissement --}}
<div style="background:#FEF2F2;border:1px solid #FCA5A5;border-radius:10px;
            padding:12px 16px;margin-bottom:20px;font-size:13px;color:#991B1B;
            display:flex;align-items:flex-start;gap:10px">
    <span style="font-size:18px;flex-shrink:0">⚠️</span>
    <span>
        L'utilisateur devra se connecter avec ce nouveau mot de passe.
        Communiquez-le lui de manière sécurisée.
    </span>
</div>

{{-- Formulaire --}}
<form method="POST" action="{{ route('super-admin.utilisateurs.reset-password', $user) }}">
    @csrf

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:16px">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card" style="padding:20px">

        <div class="form-group" style="margin-bottom:16px">
            <label class="form-label">Nouveau mot de passe <span style="color:var(--red)">*</span></label>
            <div class="password-wrap">
                <input type="password"
                       name="password"
                       id="password"
                       class="form-input"
                       placeholder="Minimum 8 caractères"
                       autocomplete="new-password"
                       required>
                <button type="button" class="eye-btn" onclick="togglePwd('password')">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Confirmer le mot de passe <span style="color:var(--red)">*</span></label>
            <div class="password-wrap">
                <input type="password"
                       name="password_confirmation"
                       id="password_confirmation"
                       class="form-input"
                       placeholder="Répéter le mot de passe"
                       autocomplete="new-password"
                       required>
                <button type="button" class="eye-btn" onclick="togglePwd('password_confirmation')">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
        </div>

    </div>

    <button type="submit"
            style="width:100%;padding:14px;background:var(--orange);color:#fff;
                   border:none;border-radius:50px;font-size:16px;font-weight:700;
                   font-family:inherit;cursor:pointer;margin-top:20px;transition:opacity .15s"
            onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        Réinitialiser le mot de passe
    </button>

    <a href="{{ route('super-admin.utilisateurs') }}"
       style="display:block;text-align:center;margin-top:14px;
              font-size:14px;color:var(--text2);text-decoration:none">
        Annuler
    </a>
</form>

@endsection
