@extends('layouts.app')

@section('title', 'Super Admin — ' . $boulangerie->nom)

@section('content')

{{-- ── En-tête ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
    <a href="{{ route('super-admin.boulangeries') }}"
       style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <span style="font-size:17px;font-weight:700;color:#111827">Détail boulangerie</span>
    <div style="width:36px"></div>
</div>

{{-- ── Carte identité ── --}}
<div class="card" style="text-align:center;padding:24px 20px 20px">
    <div style="width:64px;height:64px;border-radius:18px;
                background:{{ $boulangerie->suspendu ? 'var(--red-bg)' : 'var(--orange-bg)' }};
                display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 12px">
        {{ $boulangerie->suspendu ? '🔒' : '🥖' }}
    </div>
    <div style="font-size:18px;font-weight:800;color:#111827;margin-bottom:6px">
        {{ $boulangerie->nom }}
    </div>
    <span class="badge {{ $boulangerie->suspendu ? 'badge-red' : 'badge-green' }}"
          style="font-size:13px;padding:4px 14px">
        {{ $boulangerie->suspendu ? '⛔ Suspendue' : '✅ Active' }}
    </span>
</div>

{{-- ── Boutons d'action ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">

    @if(!$boulangerie->suspendu && $proprietaire)
    <form method="POST" action="{{ route('super-admin.boulangeries.acceder', $boulangerie) }}"
          onsubmit="return confirm('Entrer dans l\'espace de {{ addslashes($boulangerie->nom) }} ?')">
        @csrf
        <button type="submit"
                style="width:100%;display:flex;align-items:center;justify-content:center;gap:6px;
                       padding:12px;border-radius:var(--radius-sm);border:none;cursor:pointer;
                       background:var(--blue);color:#fff;font-size:13px;font-weight:700">
            🔑 Accéder
        </button>
    </form>
    @else
    <div></div>
    @endif

    @if($boulangerie->suspendu)
    <form method="POST" action="{{ route('super-admin.boulangeries.reactiver', $boulangerie) }}">
        @csrf @method('PATCH')
        <button type="submit"
                style="width:100%;display:flex;align-items:center;justify-content:center;gap:6px;
                       padding:12px;border-radius:var(--radius-sm);border:none;cursor:pointer;
                       background:var(--green);color:#fff;font-size:13px;font-weight:700">
            ✅ Réactiver
        </button>
    </form>
    @else
    <form method="POST" action="{{ route('super-admin.boulangeries.suspendre', $boulangerie) }}"
          onsubmit="return confirm('Suspendre {{ addslashes($boulangerie->nom) }} ? Les utilisateurs ne pourront plus se connecter.')">
        @csrf @method('PATCH')
        <button type="submit"
                style="width:100%;display:flex;align-items:center;justify-content:center;gap:6px;
                       padding:12px;border-radius:var(--radius-sm);border:none;cursor:pointer;
                       background:var(--red);color:#fff;font-size:13px;font-weight:700">
            ⛔ Suspendre
        </button>
    </form>
    @endif

</div>

@if(session('success'))
    <div class="flash flash-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash flash-error">{{ session('error') }}</div>
@endif

{{-- ── Informations SaaS ── --}}
<div class="card">
    <div class="card-subtitle">Informations SaaS</div>

    <div class="detail-row">
        <span class="detail-row-label">📅 Date d'inscription</span>
        <span class="detail-row-value">
            {{ $boulangerie->created_at ? $boulangerie->created_at->format('d/m/Y') : '—' }}
        </span>
    </div>

    <div class="detail-row">
        <span class="detail-row-label">🕐 Dernière connexion</span>
        @if($proprietaire?->last_login_at)
            <span class="detail-row-value" style="font-size:13px">
                {{ $proprietaire->last_login_at->format('d/m/Y à H:i') }}
            </span>
        @else
            <span style="font-size:13px;color:var(--text2)">Jamais connecté</span>
        @endif
    </div>

    <div class="detail-row" style="border-bottom:none">
        <span class="detail-row-label">📊 Statut</span>
        <span class="badge {{ $boulangerie->suspendu ? 'badge-red' : 'badge-green' }}">
            {{ $boulangerie->suspendu ? 'Suspendue' : 'Actif' }}
        </span>
    </div>
</div>

{{-- ── Coordonnées ── --}}
<div class="card">
    <div class="card-subtitle">Coordonnées</div>

    @if($proprietaire)
        @php $nbBoulangeries = $proprietaire->boulangeries()->count(); @endphp
        <div class="detail-row">
            <span class="detail-row-label">👤 Propriétaire</span>
            <span class="detail-row-value">
                {{ $proprietaire->name }}
                @if($nbBoulangeries > 1)
                    <a href="{{ route('super-admin.boulangeries') }}" style="font-size:12px;font-weight:500;color:var(--orange);text-decoration:none">
                        (+{{ $nbBoulangeries - 1 }} autre{{ $nbBoulangeries - 1 > 1 ? 's' : '' }})
                    </a>
                @endif
            </span>
        </div>
    @endif

    @if($boulangerie->telephone)
        <div class="detail-row">
            <span class="detail-row-label">📞 Téléphone</span>
            <span class="detail-row-value">{{ $boulangerie->telephone }}</span>
        </div>
    @endif

    @if($boulangerie->adresse)
        <div class="detail-row">
            <span class="detail-row-label">📍 Adresse</span>
            <span class="detail-row-value">{{ $boulangerie->adresse }}</span>
        </div>
    @endif

    @if($boulangerie->email)
        <div class="detail-row">
            <span class="detail-row-label">✉️ Email</span>
            <span class="detail-row-value">{{ $boulangerie->email }}</span>
        </div>
    @endif

    <div class="detail-row" style="border-bottom:none">
        <span class="detail-row-label">🥖 Prix pain</span>
        <span class="detail-row-value">{{ number_format($boulangerie->prix_pain ?? 0, 0, ',', ' ') }} FCFA</span>
    </div>
</div>

{{-- ── Utilisateurs ── --}}
@php
    $utilisateursBoulangerie = $proprietaire ? collect([$proprietaire])->merge($gerants) : $gerants;
@endphp
<div class="card">
    <div class="card-subtitle">Utilisateurs ({{ $utilisateursBoulangerie->count() }})</div>

    @forelse($utilisateursBoulangerie as $user)
        <div style="display:flex;align-items:center;gap:12px;padding:11px 0;
                    {{ $loop->last ? '' : 'border-bottom:1px solid var(--border);' }}">
            <div style="width:40px;height:40px;border-radius:50%;background:var(--orange-soft);
                        display:flex;align-items:center;justify-content:center;
                        font-size:15px;font-weight:700;color:var(--orange-dark);flex-shrink:0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:14px;font-weight:600;color:#111827">{{ $user->name }}</div>
                <div style="font-size:12px;color:var(--text2);margin-top:1px">
                    {{ $user->telephone ?? $user->email ?? '—' }}
                </div>
                @if($user->last_login_at)
                    <div style="font-size:11px;color:var(--text2);margin-top:1px">
                        🕐 {{ $user->last_login_at->format('d/m/Y H:i') }}
                    </div>
                @endif
            </div>
            @if($user->role)
                <span class="badge {{ $user->role->nom === 'proprietaire' ? 'badge-orange' : 'badge-blue' }}">
                    {{ $user->role->nom === 'proprietaire' ? 'Propriétaire' : 'Gérant' }}
                </span>
            @endif
        </div>
    @empty
        <div style="text-align:center;padding:16px 0;color:var(--text2);font-size:14px">
            Aucun utilisateur.
        </div>
    @endforelse
</div>

@endsection
