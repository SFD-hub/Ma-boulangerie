@extends('layouts.app')

@section('title', 'Caisse du jour')

@section('content')

    <a href="{{ route('dashboard') }}" class="back-link">← Accueil</a>

    <h1 class="section-title" style="margin:12px 0 16px">Caisse du jour</h1>

    {{-- Choix de la date --}}
    <div style="margin-bottom:20px">
        <input type="date" value="{{ $date }}" max="{{ date('Y-m-d') }}"
               onchange="window.location = '{{ route('caisse.index') }}?date=' + this.value"
               style="padding:8px 12px;border-radius:10px;border:1px solid var(--border);font-size:13px;color:#111827;background:#FFFFFF">
    </div>

    {{-- ── ENCAISSÉ ── --}}
    <div style="font-size:11px;font-weight:800;color:#059669;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;padding:0 4px">
        Encaissé
    </div>

    <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">

        @forelse($encaissements as $e)
            @php
                $badgeClass = match($e['type']) {
                    'livreur' => 'badge-blue',
                    'client'  => 'badge-orange',
                    default   => 'badge-purple',
                };
            @endphp
            <a href="{{ $e['route'] }}"
               style="display:flex;align-items:center;gap:14px;padding:14px 16px;text-decoration:none;border-bottom:1px solid #E5E7EB">
                <div class="avatar" style="width:36px;height:36px;font-size:14px">{{ strtoupper(substr($e['nom'], 0, 1)) }}</div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:14px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        {{ $e['nom'] }}
                    </div>
                </div>
                <span class="badge {{ $badgeClass }}">{{ $e['typeLabel'] }}</span>
                <span style="font-size:14px;font-weight:700;color:#111827;flex-shrink:0">
                    {{ number_format($e['montant'], 0, ',', ' ') }}&nbsp;FCFA
                </span>
            </a>
        @empty
        @endforelse

        @if($depotJour['quantite'] > 0)
            <a href="{{ $depotJour['route'] }}"
               style="display:flex;align-items:center;gap:14px;padding:14px 16px;text-decoration:none;border-bottom:1px solid #E5E7EB;background:#FFFBEB">
                <div style="width:36px;height:36px;border-radius:50%;background:#FEF3C7;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0">
                    🏪
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:14px;font-weight:600;color:#111827">Dépôt — total du jour</div>
                    <div style="font-size:12px;color:#9CA3AF;margin-top:1px">
                        {{ $depotJour['quantite'] }} pain{{ $depotJour['quantite'] > 1 ? 's' : '' }} vendu{{ $depotJour['quantite'] > 1 ? 's' : '' }}
                    </div>
                </div>
                <span style="font-size:14px;font-weight:700;color:#111827;flex-shrink:0">
                    {{ number_format($depotJour['montant'], 0, ',', ' ') }}&nbsp;FCFA
                </span>
            </a>
        @endif

        @if($encaissements->isEmpty() && $depotJour['quantite'] === 0)
            <div class="empty-state" style="padding:24px 16px">
                <p>Aucun encaissement le {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.</p>
            </div>
        @else
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:#F0FDF4">
                <span style="font-size:14px;font-weight:700;color:#065F46">Total encaissé</span>
                <span style="font-size:15px;font-weight:800;color:#059669">
                    {{ number_format($totalEncaisse, 0, ',', ' ') }}&nbsp;FCFA
                </span>
            </div>
        @endif

    </div>

    {{-- ── DÉPENSES ── --}}
    <div style="font-size:11px;font-weight:800;color:#EF4444;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;padding:0 4px">
        Dépenses
    </div>

    <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">

        @forelse($depenses as $d)
            <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-bottom:1px solid #E5E7EB">
                <x-depense-icone :categorie="$d['categorie']" />
                <div style="flex:1;min-width:0">
                    <div style="font-size:14px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        {{ $d['libelle'] }}
                    </div>
                </div>
                <span style="font-size:14px;font-weight:700;color:#111827;flex-shrink:0">
                    {{ number_format($d['montant'], 0, ',', ' ') }}&nbsp;FCFA
                </span>
            </div>
        @empty
            <div class="empty-state" style="padding:24px 16px">
                <p>Aucune dépense le {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.</p>
            </div>
        @endforelse

        @if($depenses->isNotEmpty())
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:#FEF2F2">
                <span style="font-size:14px;font-weight:700;color:#991B1B">Total dépenses</span>
                <span style="font-size:15px;font-weight:800;color:#EF4444">
                    {{ number_format($totalDepense, 0, ',', ' ') }}&nbsp;FCFA
                </span>
            </div>
        @endif

    </div>

    {{-- ── SOLDE DU JOUR ── --}}
    <div style="background:{{ $solde >= 0 ? '#F0FDF4' : '#FEF2F2' }};border-radius:14px;padding:18px 20px;display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:15px;font-weight:700;color:{{ $solde >= 0 ? '#065F46' : '#991B1B' }}">Solde du jour</span>
        <span style="font-size:20px;font-weight:800;color:{{ $solde >= 0 ? '#059669' : '#EF4444' }}">
            {{ $solde >= 0 ? '+' : '' }}{{ number_format($solde, 0, ',', ' ') }}&nbsp;FCFA
        </span>
    </div>

@endsection
