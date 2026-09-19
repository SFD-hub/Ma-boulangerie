@extends('layouts.app')

@section('title', 'Encaissé du jour')

@section('content')

    <a href="{{ route('dashboard') }}" class="back-link">← Accueil</a>

    <h1 class="section-title" style="margin:12px 0 16px">Encaissé du jour</h1>

    {{-- Choix de la date --}}
    <div style="margin-bottom:16px">
        <input type="date" value="{{ $date }}" max="{{ date('Y-m-d') }}"
               onchange="window.location = '{{ route('encaisse.index') }}?date=' + this.value"
               style="padding:8px 12px;border-radius:10px;border:1px solid var(--border);font-size:13px;color:#111827;background:#FFFFFF">
    </div>

    {{-- ── Carte total ── --}}
    <div style="background:#D1FAE5;border-radius:18px;padding:18px 14px;margin-bottom:20px">
        <div style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:10px;line-height:1.3">
            Total encaissé
        </div>
        <div style="font-size:34px;font-weight:800;color:#111827;line-height:1">
            {{ number_format($totalEncaisse, 0, ',', ' ') }}
        </div>
        <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">FCFA</div>
    </div>

    {{-- ── Détail ── --}}
    @if($encaissements->isEmpty() && $depotJour['quantite'] === 0)
        <div class="card">
            <div class="empty-state">
                <p style="font-size:32px;margin-bottom:8px">💰</p>
                <p>Aucun encaissement le {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.</p>
            </div>
        </div>
    @else
        <div class="card">
            @foreach($encaissements as $e)
                @php
                    $badgeClass = match($e['type']) {
                        'livreur' => 'badge-blue',
                        'client'  => 'badge-orange',
                        default   => 'badge-purple',
                    };
                @endphp
                <a href="{{ $e['route'] }}" class="list-item" style="text-decoration:none">
                    <div class="avatar">{{ strtoupper(substr($e['nom'], 0, 1)) }}</div>
                    <div class="list-item-body">
                        <div class="list-item-name">{{ $e['nom'] }}</div>
                    </div>
                    <div class="list-item-right" style="display:flex;align-items:center;gap:8px">
                        <span class="badge {{ $badgeClass }}">{{ $e['typeLabel'] }}</span>
                        <span style="font-size:14px;font-weight:700;color:#111827">
                            {{ number_format($e['montant'], 0, ',', ' ') }}&nbsp;FCFA
                        </span>
                    </div>
                </a>
            @endforeach

            @if($depotJour['quantite'] > 0)
                <a href="{{ $depotJour['route'] }}" class="list-item" style="text-decoration:none">
                    <div style="width:44px;height:44px;border-radius:50%;background:#FEF3C7;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">
                        🏪
                    </div>
                    <div class="list-item-body">
                        <div class="list-item-name">Boutique — total du jour</div>
                        <div class="list-item-sub">
                            {{ $depotJour['quantite'] }} pain{{ $depotJour['quantite'] > 1 ? 's' : '' }} vendu{{ $depotJour['quantite'] > 1 ? 's' : '' }}
                        </div>
                    </div>
                    <div class="list-item-right">
                        <span style="font-size:14px;font-weight:700;color:#111827">
                            {{ number_format($depotJour['montant'], 0, ',', ' ') }}&nbsp;FCFA
                        </span>
                    </div>
                </a>
            @endif
        </div>
    @endif

@endsection
