@extends('layouts.app')

@section('title', 'Achats')

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;margin-bottom:22px">
        <div style="flex:1">
            <a href="{{ route('dashboard') }}"
               style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
        <span style="font-size:17px;font-weight:700;color:#111827">Achats</span>
        <div style="flex:1;display:flex;justify-content:flex-end">
            <a href="{{ route('achats-matieres-premieres.create') }}"
               style="background:#F97316;color:#FFFFFF;border-radius:20px;padding:8px 16px;font-size:14px;font-weight:700;text-decoration:none">
                Ajouter
            </a>
        </div>
    </div>

    {{-- ── Les 3 derniers achats ; « Voir plus » ouvre l'historique complet ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
        <span style="font-size:15px;font-weight:700;color:#111827">Historique des achats</span>
        @if($achats->isNotEmpty())
            <a href="{{ route('achats-matieres-premieres.historique') }}"
               style="font-size:13px;font-weight:600;color:#F97316;text-decoration:none">Voir plus</a>
        @endif
    </div>

    @if($achats->isEmpty())

        <div style="text-align:center;padding:48px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">🛒</div>
            Aucun achat enregistré.
        </div>

    @else

        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">
            @foreach($achats as $achat)
                @include('achats-matieres-premieres._ligne')
            @endforeach
        </div>

    @endif

@endsection
