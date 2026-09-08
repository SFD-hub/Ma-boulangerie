@extends('layouts.app')

@section('title', 'Dépenses')

@section('content')


    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('dashboard') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Dépenses</span>
        <a href="{{ route('depenses.create') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none;font-size:24px;font-weight:300;line-height:1">
            +
        </a>
    </div>

    @if($depenses->isEmpty())

        <div style="text-align:center;padding:48px 0 32px;color:#9CA3AF;font-size:14px">
            <div style="font-size:42px;margin-bottom:12px">💸</div>
            Aucune dépense enregistrée.
        </div>

    @else

        {{-- ── Liste des dépenses récentes ── --}}
        <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:12px">
            @foreach($depenses as $depense)
                <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">

                    <x-depense-icone :categorie="$depense->categorie" />

                    {{-- Libellé --}}
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $depense->libelle }}
                        </div>
                    </div>

                    {{-- Montant + Date --}}
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-size:14px;font-weight:700;color:#111827">
                            {{ number_format($depense->montant, 0, ',', ' ') }}&nbsp;FCFA
                        </div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px">
                            {{ $depense->date_depense->format('d/m/Y') }}
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        {{-- ── Voir plus ── --}}
        <div style="text-align:right;margin-bottom:16px">
            <a href="{{ route('depenses.historique') }}"
               style="font-size:13px;font-weight:600;color:#F97316;text-decoration:none">
                Voir plus
            </a>
        </div>

    @endif

    {{-- ── Total dépenses ── --}}
    <div style="background:#F97316;border-radius:14px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:15px;font-weight:600;color:#FFFFFF">Total dépenses</span>
        <span style="font-size:16px;font-weight:800;color:#FFFFFF">{{ number_format($totalGlobal, 0, ',', ' ') }}&nbsp;FCFA</span>
    </div>

@endsection
