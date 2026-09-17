@extends('layouts.app')

@section('title', 'Distribution du jour')

@section('content')

    <a href="{{ route('clients.index') }}" class="back-link">← Clients</a>

    <h1 class="section-title" style="margin:12px 0 16px">Distribution du jour</h1>

    {{-- Choix de la date --}}
    <div style="margin-bottom:16px">
        <input type="date" value="{{ $date }}"
               onchange="window.location = '{{ route('clients.distribution') }}?date=' + this.value"
               style="padding:8px 12px;border-radius:10px;border:1px solid var(--border);font-size:13px;color:#111827;background:#FFFFFF">
    </div>

    {{-- Cartes de stats — même style que l'Accueil --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px">
        <div style="background:#D1FAE5;border-radius:18px;padding:18px 14px">
            <div style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:10px;line-height:1.3">
                Produit
            </div>
            <div style="font-size:34px;font-weight:800;color:#111827;line-height:1">
                {{ number_format($totalProduit, 0, ',', ' ') }}
            </div>
            <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">pains</div>
        </div>

        <div style="background:#DBEAFE;border-radius:18px;padding:18px 14px">
            <div style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:10px;line-height:1.3">
                Distribué
            </div>
            <div style="font-size:34px;font-weight:800;color:#111827;line-height:1">
                {{ number_format($totalDistribue, 0, ',', ' ') }}
            </div>
            <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">pains</div>
        </div>
    </div>

    {{-- Liste triée par quantité --}}
    @if($lignes->isEmpty())
        <div class="card">
            <div class="empty-state">
                <p style="font-size:32px;margin-bottom:8px">📅</p>
                <p>Personne n'a pris de pain le {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.</p>
            </div>
        </div>
    @else
        <div class="card">
            @foreach($lignes as $l)
                @php
                    $badgeClass = match($l['type']) {
                        'livreur' => 'badge-blue',
                        'client'  => 'badge-orange',
                        default   => 'badge-purple',
                    };
                @endphp
                <a href="{{ $l['route'] }}" class="list-item" style="text-decoration:none">
                    <div class="avatar">{{ strtoupper(substr($l['nom'], 0, 1)) }}</div>
                    <div class="list-item-body">
                        <div class="list-item-name">{{ $l['nom'] }}</div>
                    </div>
                    <div class="list-item-right" style="display:flex;align-items:center;gap:8px">
                        <span class="badge {{ $badgeClass }}">{{ $l['typeLabel'] }}</span>
                        <span style="font-size:14px;font-weight:700;color:#111827">
                            {{ $l['quantite'] }} pain{{ $l['quantite'] > 1 ? 's' : '' }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

@endsection
