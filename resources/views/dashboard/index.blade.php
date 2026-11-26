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
                {{ \App\Support\Nombre::painsEntiers($painsDistribues) }}
            </div>
            <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">{{ \App\Support\Nombre::painsLegende($painsDistribues) }}</div>
        </a>

        {{-- Encaissé du jour — fond orange/beige --}}
        <a href="{{ route('encaisse.index') }}"
           style="background:#FFEDD5;border-radius:18px;padding:18px 14px;text-decoration:none;display:block">
            <div style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:10px;line-height:1.3">
                Encaissé du jour
            </div>
            <div style="font-size:34px;font-weight:800;color:#111827;line-height:1">
                {{ number_format($encaisseJour, 0, ',', ' ') }}
            </div>
            <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">FCFA</div>
        </a>

        {{-- Dépenses du jour — fond violet --}}
        <a href="{{ route('depenses.jour') }}"
           style="background:#EDE9FE;border-radius:18px;padding:18px 14px;text-decoration:none;display:block">
            <div style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:10px;line-height:1.3">
                Dépenses du jour
            </div>
            <div style="font-size:34px;font-weight:800;color:#111827;line-height:1">
                {{ number_format($depenseJour, 0, ',', ' ') }}
            </div>
            <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">FCFA</div>
        </a>

    </div>

    {{-- ── Distribution du jour ── --}}
    <div style="margin-bottom:20px;background:#FFFFFF;border-radius:18px;padding:18px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07)">

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
            @foreach(collect($distributionsJour)->take(4) as $d)
                @php
                    $badgeClass = match($d['type']) {
                        'livreur' => 'badge-blue',
                        'client'  => 'badge-orange',
                        'vendeur' => 'badge-green',
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
                        {{ \App\Support\Nombre::pains($d['quantite']) }}
                    </span>
                </a>
            @endforeach
        @endif

    </div>


    {{-- ── Alertes stock ── --}}
    @if($alertes->isNotEmpty())
        <div style="margin-bottom:20px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                <span style="font-size:15px;font-weight:700;color:#111827">Alertes de stock faible</span>
                <a href="{{ route('matieres-premieres.index') }}"
                   style="font-size:13px;font-weight:600;color:#F97316;text-decoration:none">Voir plus</a>
            </div>

            <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07)">
                @foreach($alertes->take(4) as $alerte)
                    <a href="{{ route('matieres-premieres.show', $alerte) }}"
                       style="display:flex;align-items:center;gap:12px;padding:12px 16px;text-decoration:none;{{ $loop->last ? '' : 'border-bottom:1px solid #F3F4F6;' }}">
                        <div style="flex-shrink:0;width:28px;height:28px;display:flex;align-items:center;justify-content:center">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#FB923C">
                                <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span style="flex:1;font-size:14px;font-weight:600;color:#111827;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                            {{ $alerte->nom }}
                        </span>
                        <span style="font-size:13px;font-weight:700;color:#EF4444;flex-shrink:0">
                            {{ \App\Support\Nombre::qte($alerte->stock_actuel) }} {{ $alerte->uniteLibelle($alerte->stock_actuel) }} restant{{ (float) $alerte->stock_actuel > 1 ? 's' : '' }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

@endsection
