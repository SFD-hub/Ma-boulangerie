@extends('layouts.app')

@section('title', 'Super Admin — Tableau de bord')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
    <span style="font-size:17px;font-weight:700;color:#111827">Tableau de bord global</span>
    <span style="font-size:12px;font-weight:600;background:#EFF6FF;color:#1D4ED8;padding:4px 10px;border-radius:50px">SUPER ADMIN</span>
</div>

{{-- ── Cartes stats ── --}}
<div class="stat-grid" style="grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">

    <div class="stat-card orange">
        <div class="stat-label">Boulangeries</div>
        <div class="stat-value orange">{{ $totalBoulangeries }}</div>
        <div class="stat-unit">enregistrées</div>
    </div>

    <div class="stat-card blue">
        <div class="stat-label">Utilisateurs</div>
        <div class="stat-value blue">{{ $totalUtilisateurs }}</div>
        <div class="stat-unit">actifs</div>
    </div>

    <div class="stat-card" style="background:var(--green-bg)">
        <div class="stat-label">Propriétaires</div>
        <div class="stat-value" style="color:var(--green)">{{ $totalProprietaires }}</div>
        <div class="stat-unit">comptes</div>
    </div>

    <div class="stat-card" style="background:var(--yellow-bg)">
        <div class="stat-label">Gérants</div>
        <div class="stat-value" style="color:var(--yellow)">{{ $totalGerants }}</div>
        <div class="stat-unit">comptes</div>
    </div>

</div>

{{-- ── Dernières boulangeries ── --}}
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
        <div class="card-title" style="margin-bottom:0">Dernières boulangeries</div>
        <a href="{{ route('super-admin.boulangeries') }}"
           style="font-size:13px;font-weight:600;color:var(--orange);text-decoration:none">
            Voir tout
        </a>
    </div>

    @forelse($dernieresBoulangeries as $b)
        <a href="{{ route('super-admin.boulangeries.show', $b) }}"
           style="display:flex;align-items:center;gap:12px;padding:11px 0;
                  {{ $loop->last ? '' : 'border-bottom:1px solid var(--border);' }}
                  text-decoration:none;color:inherit">
            <div style="width:40px;height:40px;background:var(--orange-bg);border-radius:10px;
                        display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
                🏪
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:14px;font-weight:600;color:#111827;
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ $b->nom }}
                </div>
                <div style="font-size:12px;color:var(--text2);margin-top:1px">
                    {{ $b->created_at ? $b->created_at->format('d/m/Y') : '—' }}
                </div>
            </div>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                 viewBox="0 0 24 24" style="color:var(--text2);flex-shrink:0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    @empty
        <div style="text-align:center;padding:20px 0;color:var(--text2);font-size:14px">
            Aucune boulangerie enregistrée.
        </div>
    @endforelse
</div>

@endsection
