@extends('layouts.app')

@section('title', 'Dépenses')

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
        <span style="font-size:17px;font-weight:700;color:#111827">Dépenses</span>
        <div style="flex:1;display:flex;justify-content:flex-end">
            <a href="{{ route('depenses.create') }}"
               style="background:#F97316;color:#FFFFFF;border-radius:20px;padding:8px 16px;font-size:14px;font-weight:700;text-decoration:none">
                Ajouter
            </a>
        </div>
    </div>

    {{-- ── Période ── --}}
    @include('partials.periode', ['action' => route('depenses.index')])

    {{-- ── Total de la période ── --}}
    <div style="background:#F97316;border-radius:14px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <div>
            <div style="font-size:15px;font-weight:600;color:#FFFFFF">Total dépenses</div>
            <div style="font-size:12px;color:rgba(255,255,255,.8);margin-top:2px">
                du {{ \Carbon\Carbon::parse($du)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($au)->format('d/m/Y') }}
            </div>
        </div>
        <span style="font-size:16px;font-weight:800;color:#FFFFFF">{{ number_format($totalPeriode, 0, ',', ' ') }}&nbsp;FCFA</span>
    </div>

    @if($groupes->isEmpty())

        <div style="text-align:center;padding:48px 0 32px;color:#9CA3AF;font-size:14px">
            <div style="font-size:42px;margin-bottom:12px">💸</div>
            Aucune dépense sur cette période.
        </div>

    @else

        {{-- ── Une carte par catégorie : toucher pour voir le détail ── --}}
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">
            @foreach($groupes as $groupe)
                <a href="{{ route('depenses.categorie', ['categorie' => $groupe['categorie'], 'du' => $du, 'au' => $au]) }}"
                   style="display:flex;align-items:center;gap:12px;padding:14px 16px;text-decoration:none;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">
                    <x-depense-icone :categorie="$groupe['categorie']" />
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:700;color:#111827">{{ $groupe['libelle'] }}</div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px">
                            {{ $groupe['nombre'] }} dépense{{ $groupe['nombre'] > 1 ? 's' : '' }}
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-size:14px;font-weight:800;color:#111827">{{ number_format($groupe['total'], 0, ',', ' ') }}&nbsp;FCFA</div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px">
                            {{ $totalPeriode > 0 ? number_format($groupe['total'] / $totalPeriode * 100, 0) : 0 }}&nbsp;%
                        </div>
                    </div>
                    <svg width="16" height="16" fill="none" stroke="#9CA3AF" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endforeach
        </div>

    @endif

    <div style="text-align:center;margin-bottom:8px">
        <a href="{{ route('depenses.historique') }}"
           style="font-size:13px;font-weight:600;color:#F97316;text-decoration:none">
            Toutes les dépenses
        </a>
    </div>

@endsection
