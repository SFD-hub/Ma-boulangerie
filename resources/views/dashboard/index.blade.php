@extends('layouts.app')

@section('title', 'Accueil')

@section('content')

    {{-- ── En-tête ── --}}
    <div style="margin-bottom:20px">
        <div style="font-size:20px;font-weight:700;color:#111827;line-height:1.3">
            {{ now()->hour < 12 ? 'Bonjour' : 'Bonsoir' }}, {{ auth()->user()->name }} 👋
        </div>
        <div style="font-size:13px;color:#9CA3AF;margin-top:3px">
            Aujourd'hui, {{ \Carbon\Carbon::today()->locale('fr')->isoFormat('DD MMMM YYYY') }}
        </div>
    </div>

    {{-- ── Alertes stock ── --}}
    @foreach($alertes as $alerte)
        <div class="alert-strip" style="margin-bottom:12px">
            ⚠️ Stock {{ $alerte['nom'] }} faible : {{ \App\Support\Nombre::qte($alerte['stock']) }} {{ $alerte['unite'] }} (seuil : {{ \App\Support\Nombre::qte($alerte['seuil']) }})
        </div>
    @endforeach

    {{-- ── Cartes statistiques ── --}}
    <div class="dashboard-stat-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px">

        {{-- Production du jour — fond vert --}}
        <div style="background:#D1FAE5;border-radius:18px;padding:18px 14px">
            <div style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:10px;line-height:1.3">
                Production du jour
            </div>
            <div style="font-size:34px;font-weight:800;color:#111827;line-height:1">
                {{ number_format($productionJour, 0, ',', ' ') }}
            </div>
            <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">pains</div>
        </div>

        {{-- Pains distribués — fond bleu, renvoie vers le détail du jour --}}
        <a href="{{ route('clients.distribution') }}"
           style="background:#DBEAFE;border-radius:18px;padding:18px 14px;text-decoration:none;display:block">
            <div style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:10px;line-height:1.3">
                Pains distribués
            </div>
            <div style="font-size:34px;font-weight:800;color:#111827;line-height:1">
                {{ number_format($painsDistribues, 0, ',', ' ') }}
            </div>
            <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">pains</div>
        </a>

        {{-- Stock Farine — fond orange/beige --}}
        <div style="background:#FFEDD5;border-radius:18px;padding:18px 14px{{ $farineStock <= $farineSeuil && $farineSeuil > 0 ? ';outline:2px solid #FBBF24;outline-offset:-2px' : '' }}">
            <div style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:10px;line-height:1.3">
                Stock Farine
            </div>
            <div style="font-size:34px;font-weight:800;line-height:1;color:{{ $farineStock <= $farineSeuil && $farineSeuil > 0 ? '#EF4444' : '#111827' }}">
                {{ \App\Support\Nombre::qte($farineStock) }}
            </div>
            <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">sacs</div>
        </div>

        {{-- Stock Levure — fond violet --}}
        <div style="background:#EDE9FE;border-radius:18px;padding:18px 14px{{ $levureStock <= $levureSeuil && $levureSeuil > 0 ? ';outline:2px solid #FBBF24;outline-offset:-2px' : '' }}">
            <div style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:10px;line-height:1.3">
                Stock Levure
            </div>
            <div style="font-size:34px;font-weight:800;line-height:1;color:{{ $levureStock <= $levureSeuil && $levureSeuil > 0 ? '#EF4444' : '#111827' }}">
                {{ \App\Support\Nombre::qte($levureStock) }}
            </div>
            <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">paquets</div>
        </div>

    </div>

    {{-- ── Distribution du jour ── --}}
    <div style="background:#FFFFFF;border-radius:18px;padding:18px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07)">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <span style="font-size:16px;font-weight:700;color:#111827">Distribution du jour</span>
            <a href="{{ route('clients.distribution') }}"
               style="font-size:13px;font-weight:600;color:#F97316;text-decoration:none">
                Voir plus
            </a>
        </div>

        @if(empty($distributionsJour))
            <div style="text-align:center;padding:24px 0;color:#9CA3AF;font-size:14px">
                Personne n'a pris de pain aujourd'hui.
            </div>
        @else
            @foreach($distributionsJour as $d)
                @php
                    $badgeClass = match($d['type']) {
                        'livreur' => 'badge-blue',
                        'client'  => 'badge-orange',
                        default   => 'badge-purple',
                    };
                @endphp
                <a href="{{ $d['route'] }}"
                   style="display:flex;align-items:center;gap:14px;padding:11px 0;text-decoration:none;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">
                    <div class="avatar">{{ strtoupper(substr($d['nom'], 0, 1)) }}</div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:600;color:#111827;line-height:1.3">
                            {{ $d['nom'] }}
                        </div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px">
                            {{ $d['at']->format('H:i') }}
                        </div>
                    </div>
                    <span class="badge {{ $badgeClass }}">{{ $d['typeLabel'] }}</span>
                    <span style="font-size:14px;font-weight:700;color:#111827">
                        {{ $d['quantite'] }} pain{{ $d['quantite'] > 1 ? 's' : '' }}
                    </span>
                </a>
            @endforeach
        @endif

    </div>

@endsection
