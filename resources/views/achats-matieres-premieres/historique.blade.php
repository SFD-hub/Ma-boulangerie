@extends('layouts.app')

@section('title', 'Historique des achats')

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;margin-bottom:22px">
        <div style="flex:1">
            <a href="{{ $matiere ? route('matieres-premieres.show', $matiere) : route('achats-matieres-premieres.index') }}"
               style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
        <span style="font-size:17px;font-weight:700;color:#111827">Historique des achats</span>
        <div style="flex:1"></div>
    </div>

    {{-- ── Filtres : produit et période (jusqu'à 1 an) ── --}}
    <form method="GET" action="{{ route('achats-matieres-premieres.historique') }}"
          style="background:#FFFFFF;border-radius:16px;padding:14px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">
        <div style="margin-bottom:10px">
            <label style="display:block;font-size:12px;font-weight:600;color:#6B7280;margin-bottom:4px">Produit</label>
            <select class="form-input" name="matiere" onchange="this.form.submit()">
                <option value="">Tous les produits</option>
                @foreach($matieres as $m)
                    <option value="{{ $m->id }}" {{ $matiere && $matiere->id === $m->id ? 'selected' : '' }}>{{ $m->nom }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
            <div style="flex:1;min-width:130px">
                <label style="display:block;font-size:12px;font-weight:600;color:#6B7280;margin-bottom:4px">Du</label>
                <input class="form-input" type="date" name="du" value="{{ $du }}" max="{{ date('Y-m-d') }}">
            </div>
            <div style="flex:1;min-width:130px">
                <label style="display:block;font-size:12px;font-weight:600;color:#6B7280;margin-bottom:4px">Au</label>
                <input class="form-input" type="date" name="au" value="{{ $au }}" max="{{ date('Y-m-d') }}">
            </div>
            <button type="submit" class="btn btn-primary" style="height:44px">Afficher</button>
        </div>
        @php
            $base = $matiere ? ['matiere' => $matiere->id] : [];
            $raccourcis = [
                'Ce mois'      => [now()->startOfMonth()->toDateString(), now()->toDateString()],
                'Mois dernier' => [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()],
                '12 derniers mois' => [now()->subYear()->addDay()->toDateString(), now()->toDateString()],
            ];
        @endphp
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
            @foreach($raccourcis as $nom => [$d1, $d2])
                <a href="{{ route('achats-matieres-premieres.historique', $base + ['du' => $d1, 'au' => $d2]) }}"
                   style="font-size:12px;font-weight:600;padding:6px 12px;border-radius:20px;text-decoration:none;{{ $du === $d1 && $au === $d2 ? 'background:#F97316;color:#FFFFFF' : 'background:#F3F4F6;color:#374151' }}">
                    {{ $nom }}
                </a>
            @endforeach
        </div>
        @if($periodeLimitee)
            <div style="font-size:12px;color:#B45309;margin-top:10px">La période est limitée à 1 an : le début a été ajusté.</div>
        @endif
    </form>

    @if($achats->isEmpty())

        <div style="text-align:center;padding:48px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">📦</div>
            Aucun achat sur cette période.
        </div>

    @else

        {{-- ── Liste des achats ── --}}
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">

            @foreach($achats as $achat)
                @include('achats-matieres-premieres._ligne')
            @endforeach

        </div>

        @include('partials.pagination', ['paginator' => $achats])

        {{-- ── Total achats ── --}}
        <div style="background:#F97316;border-radius:14px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:15px;font-weight:600;color:#FFFFFF">Total achats</div>
                <div style="font-size:12px;color:rgba(255,255,255,.8);margin-top:2px">
                    {{ $nombre }} achat{{ $nombre > 1 ? 's' : '' }} · du {{ \Carbon\Carbon::parse($du)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($au)->format('d/m/Y') }}
                </div>
            </div>
            <span style="font-size:16px;font-weight:800;color:#FFFFFF">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
        </div>

    @endif

@endsection
