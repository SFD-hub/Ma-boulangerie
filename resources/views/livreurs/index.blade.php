@extends('layouts.app')

@section('title', 'Livreurs')

@section('content')

    <div class="section-actions">
        <h1 class="section-title">Livreurs</h1>
        <a href="{{ route('livreurs.create') }}" class="btn btn-primary btn-sm">+ Ajouter</a>
    </div>

    @if($livreurs->isEmpty())
        <div class="card">
            <div class="empty-state">
                <p style="font-size:32px;margin-bottom:8px">🚴</p>
                <p>Aucun livreur enregistré.</p>
                <a href="{{ route('livreurs.create') }}" class="btn btn-primary btn-sm" style="margin-top:14px">
                    + Ajouter un livreur
                </a>
            </div>
        </div>
    @else
        <div class="card">
            @foreach($livreurs as $livreur)
                <a href="{{ route('livreurs.show', $livreur) }}" class="list-item" style="text-decoration:none">
                    <div class="avatar">{{ strtoupper(substr($livreur->prenom, 0, 1)) }}</div>
                    <div class="list-item-body">
                        <div class="list-item-name">{{ $livreur->prenom }} {{ $livreur->nom }}</div>
                        <div class="list-item-sub">{{ $livreur->telephone }}</div>
                    </div>
                    <div class="list-item-right" style="display:flex;align-items:center;gap:10px">
                        <span class="badge {{ $livreur->actif ? 'badge-green' : 'badge-gray' }}">
                            {{ $livreur->actif ? 'Actif' : 'Inactif' }}
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
