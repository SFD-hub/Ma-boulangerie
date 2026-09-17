@extends('layouts.app')

@section('title', 'Clients')

@section('content')

    <div class="section-actions">
        <h1 class="section-title">Clients</h1>

        <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">+ Ajouter</a>
    </div>

    <a href="{{ route('clients.distribution') }}"
       style="display:flex;align-items:center;gap:8px;background:#EFF6FF;border-radius:12px;padding:12px 16px;
              margin-bottom:16px;text-decoration:none;color:#1D4ED8;font-size:14px;font-weight:600">
        📅 Distribution du jour
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
             style="width:14px;height:14px;margin-left:auto">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </a>

    {{-- Filtre par type --}}
    <div style="display:flex;gap:8px;margin-bottom:16px;overflow-x:auto;padding-bottom:2px">
        <button type="button" class="client-filter-btn" data-type="tous" onclick="filterClients('tous')"
                style="flex-shrink:0;padding:8px 16px;border-radius:20px;border:1px solid var(--border);background:#FFFFFF;font-size:13px;font-weight:600;color:#111827;cursor:pointer">
            Tous
        </button>
        <button type="button" class="client-filter-btn" data-type="livreur" onclick="filterClients('livreur')"
                style="flex-shrink:0;padding:8px 16px;border-radius:20px;border:1px solid var(--border);background:#FFFFFF;font-size:13px;font-weight:600;color:#111827;cursor:pointer">
            Livreur
        </button>
        <button type="button" class="client-filter-btn" data-type="client" onclick="filterClients('client')"
                style="flex-shrink:0;padding:8px 16px;border-radius:20px;border:1px solid var(--border);background:#FFFFFF;font-size:13px;font-weight:600;color:#111827;cursor:pointer">
            Client
        </button>
        <button type="button" class="client-filter-btn" data-type="abonne" onclick="filterClients('abonne')"
                style="flex-shrink:0;padding:8px 16px;border-radius:20px;border:1px solid var(--border);background:#FFFFFF;font-size:13px;font-weight:600;color:#111827;cursor:pointer">
            Abonné
        </button>
    </div>

    @if($clients->isEmpty())
        <div class="card">
            <div class="empty-state">
                <p style="font-size:32px;margin-bottom:8px">👥</p>
                <p>Aucun client enregistré.</p>
                <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm" style="margin-top:14px">
                    + Ajouter
                </a>
            </div>
        </div>
    @else
        <div class="card">
            @foreach($clients as $c)
                @php
                    $badgeClass = match($c['type']) {
                        'livreur' => 'badge-blue',
                        'client'  => 'badge-orange',
                        default   => 'badge-purple',
                    };
                @endphp
                <a href="{{ $c['route'] }}" class="list-item client-item" data-type="{{ $c['type'] }}" style="text-decoration:none">
                    <div class="avatar">{{ strtoupper(substr($c['prenom'] ?: $c['nom'], 0, 1)) }}</div>
                    <div class="list-item-body">
                        <div class="list-item-name">{{ trim($c['prenom'] . ' ' . $c['nom']) }}</div>
                        <div class="list-item-sub">{{ $c['telephone'] }}</div>
                    </div>
                    <div class="list-item-right" style="display:flex;align-items:center;gap:8px">
                        <span class="badge {{ $badgeClass }}">{{ $c['typeLabel'] }}</span>
                        <span class="badge {{ $c['actif'] ? 'badge-green' : 'badge-gray' }}">
                            {{ $c['actif'] ? 'Actif' : 'Inactif' }}
                        </span>
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                             style="width:16px;height:16px;color:var(--text2);flex-shrink:0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @endforeach

            <div id="clientsEmptyFiltered" hidden style="padding:32px 16px;text-align:center;color:var(--text2);font-size:14px">
                Aucun client pour ce filtre.
            </div>
        </div>
    @endif

    <script>
        function filterClients(type) {
            document.querySelectorAll('.client-filter-btn').forEach(function (btn) {
                var active = btn.dataset.type === type;
                btn.style.background = active ? 'var(--orange)' : '#FFFFFF';
                btn.style.color = active ? '#FFFFFF' : '#111827';
                btn.style.borderColor = active ? 'var(--orange)' : 'var(--border)';
            });

            var anyVisible = false;
            document.querySelectorAll('.client-item').forEach(function (item) {
                var show = type === 'tous' || item.dataset.type === type;
                // .list-item définit display:flex dans le layout partagé, ce
                // qui l'emporterait sur l'attribut natif [hidden] (styles
                // d'origine "auteur" > feuille de style du navigateur) : on
                // bascule donc directement le style inline plutôt que .hidden.
                item.style.display = show ? 'flex' : 'none';
                if (show) anyVisible = true;
            });

            var emptyMsg = document.getElementById('clientsEmptyFiltered');
            if (emptyMsg) emptyMsg.hidden = anyVisible;
        }

        document.addEventListener('DOMContentLoaded', function () {
            var params = new URLSearchParams(window.location.search);
            var initial = params.get('type');
            if (initial && ['livreur', 'client', 'abonne'].includes(initial)) {
                filterClients(initial);
            } else {
                filterClients('tous');
            }
        });
    </script>

@endsection
