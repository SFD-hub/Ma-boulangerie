@extends('layouts.app')

@section('title', 'Historique des inventaires')

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;margin-bottom:22px">
        <div style="flex:1">
            <a href="{{ route('inventaire.index') }}"
               style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
        <span style="font-size:17px;font-weight:700;color:#111827">Écarts d'inventaire</span>
        <div style="flex:1"></div>
    </div>

    {{-- ── Période ── --}}
    @include('partials.periode', ['action' => route('inventaire.historique')])

    @if($historique->isEmpty())

        <div style="text-align:center;padding:48px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">📋</div>
            Aucun écart sur cette période.
        </div>

    @else

        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">
            @foreach($historique as $inv)
                @include('inventaire._ligne')
            @endforeach
        </div>

        @include('partials.pagination', ['paginator' => $historique])

    @endif

@endsection
