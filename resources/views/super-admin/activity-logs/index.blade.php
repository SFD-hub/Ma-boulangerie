@extends('layouts.app')

@section('title', 'Super Admin — Journal d\'activité')

@section('content')

{{-- ── En-tête ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
    <a href="{{ route('super-admin.dashboard') }}"
       style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <span style="font-size:17px;font-weight:700;color:#111827">Journal d'activité</span>
    <div style="width:36px"></div>
</div>

{{-- ── Filtres ── --}}
<form method="GET" action="{{ route('super-admin.activity-logs') }}" style="margin-bottom:14px">
    <div class="card" style="padding:14px;margin-bottom:0">

        <div class="form-group" style="margin-bottom:10px">
            <div class="input-icon-wrap">
                <span class="icon-left">🔍</span>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Rechercher une action…"
                       class="form-input"
                       style="padding-left:38px">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr auto;gap:8px;align-items:end">
            <div>
                <select name="boulangerie_id" class="form-input" style="color:{{ request('boulangerie_id') ? 'var(--text)' : 'var(--text2)' }}">
                    <option value="">Toutes les boulangeries</option>
                    @foreach($boulangeries as $b)
                        <option value="{{ $b->id }}" {{ request('boulangerie_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    style="height:46px;padding:0 16px;background:var(--orange);color:#fff;
                           border:none;border-radius:var(--radius-sm);font-weight:700;
                           font-size:14px;cursor:pointer;white-space:nowrap">
                Filtrer
            </button>
        </div>

        @if(request('search') || request('boulangerie_id'))
            <div style="margin-top:10px;text-align:center">
                <a href="{{ route('super-admin.activity-logs') }}"
                   style="font-size:13px;color:var(--text2);text-decoration:underline">
                    Effacer les filtres
                </a>
            </div>
        @endif
    </div>
</form>

{{-- ── Compteur ── --}}
<div style="font-size:13px;color:var(--text2);margin-bottom:10px;padding:0 2px">
    {{ $logs->total() }} entrée{{ $logs->total() > 1 ? 's' : '' }}
    @if(request('search') || request('boulangerie_id'))
        <span style="color:var(--orange)">• filtrées</span>
    @endif
</div>

{{-- ── Liste ── --}}
<div class="card" style="padding:0 16px">

    @forelse($logs as $log)
        @php
            $config = match($log->action) {
                'acces_support'              => ['icon' => '🔑', 'bg' => 'var(--blue-bg)',    'color' => '#1D4ED8', 'label' => 'Accès support'],
                'quitter_support'            => ['icon' => '↩️',  'bg' => '#F3F4F6',           'color' => 'var(--text2)', 'label' => 'Retour admin'],
                'suspension'                 => ['icon' => '⛔',  'bg' => 'var(--red-bg)',     'color' => '#991B1B', 'label' => 'Suspension'],
                'reactivation'               => ['icon' => '✅',  'bg' => 'var(--green-bg)',   'color' => '#065F46', 'label' => 'Réactivation'],
                'reinitialisation_mot_de_passe' => ['icon' => '🔓', 'bg' => 'var(--orange-bg)', 'color' => 'var(--orange-dark)', 'label' => 'Réinit. mdp'],
                default                      => ['icon' => '📋',  'bg' => '#F3F4F6',           'color' => 'var(--text2)', 'label' => $log->action],
            };
        @endphp

        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;
                    {{ $loop->last ? '' : 'border-bottom:1px solid var(--border);' }}">

            {{-- Icône action --}}
            <div style="width:40px;height:40px;border-radius:12px;flex-shrink:0;
                        background:{{ $config['bg'] }};display:flex;align-items:center;
                        justify-content:center;font-size:18px;margin-top:1px">
                {{ $config['icon'] }}
            </div>

            {{-- Contenu --}}
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:3px">
                    <span style="font-size:13px;font-weight:700;color:{{ $config['color'] }};
                                 background:{{ $config['bg'] }};padding:2px 8px;
                                 border-radius:50px">
                        {{ $config['label'] }}
                    </span>
                </div>
                <div style="font-size:14px;color:#111827;line-height:1.4;margin-bottom:4px">
                    {{ $log->description }}
                </div>
                <div style="font-size:12px;color:var(--text2)">
                    🕐 {{ $log->created_at->format('d/m/Y à H:i') }}
                    @if($log->user)
                        · <span style="font-weight:500">{{ $log->user->name }}</span>
                    @endif
                </div>
            </div>

        </div>
    @empty
        <div class="empty-state" style="padding:40px 0">
            <div style="font-size:32px;margin-bottom:12px">📋</div>
            <div style="font-size:15px;font-weight:600;color:#111827;margin-bottom:6px">
                Aucune activité enregistrée
            </div>
            <div style="font-size:13px;color:var(--text2)">
                Les actions du Super Admin apparaîtront ici.
            </div>
        </div>
    @endforelse

</div>

{{-- ── Pagination ── --}}
@if($logs->hasPages())
    <div style="margin-top:16px">
        {{ $logs->links() }}
    </div>
@endif

@endsection
