@extends('layouts.app')

@section('title', 'Abonnés')

@section('content')

    <div class="section-actions">
        <h1 class="section-title">Abonnés</h1>
        <a href="{{ route('clients-abonnes.create') }}" class="btn btn-primary btn-sm">+ Ajouter</a>
    </div>

    @if($clients->isEmpty())
        <div class="card">
            <div class="empty-state">
                <p style="font-size:32px;margin-bottom:8px">🛒</p>
                <p>Aucun abonné enregistré.</p>
                <a href="{{ route('clients-abonnes.create') }}" class="btn btn-primary btn-sm" style="margin-top:14px">
                    + Ajouter un abonné
                </a>
            </div>
        </div>
    @else
        <div class="card">
            @foreach($clients as $client)
                <a href="{{ route('clients-abonnes.show', $client) }}" class="list-item" style="text-decoration:none">
                    <div class="avatar">{{ strtoupper(substr($client->prenom, 0, 1)) }}</div>
                    <div class="list-item-body">
                        <div class="list-item-name">{{ $client->prenom }} {{ $client->nom }}</div>
                        <div class="list-item-sub">{{ $client->telephone }}</div>
                    </div>
                    <div class="list-item-right" style="display:flex;align-items:center;gap:10px">
                        <span class="badge {{ $client->actif ? 'badge-green' : 'badge-gray' }}">
                            {{ $client->actif ? 'Actif' : 'Inactif' }}
                        </span>
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                             style="width:16px;height:16px;color:var(--text2);flex-shrink:0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

@endsection
