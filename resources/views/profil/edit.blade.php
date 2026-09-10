@extends('layouts.app')

@section('title', 'Mon compte')

@section('header')
    <h1>Mon compte</h1>
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('profil.update') }}">
            @csrf
            @method('PUT')

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
                       value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Numéro de téléphone *</label>
                <input class="form-input" type="text" name="telephone"
                       value="{{ old('telephone', $user->telephone) }}" required>
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

            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>

@endsection
