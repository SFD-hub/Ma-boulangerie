@extends('layouts.app')

@section('title', 'Bilan - ' . $bilan->periodeLabel())

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('bilan.historique') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Bilan financier</span>
        <div style="width:36px"></div>
    </div>

    {{-- ── Titre période ── --}}
    <div style="text-align:center;font-size:15px;font-weight:700;color:#F97316;margin-bottom:20px;letter-spacing:.02em">
        {{ $bilan->periodeLabel() }}
    </div>

    {{-- ── RECETTES ── --}}
    <div style="font-size:11px;font-weight:800;color:#059669;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;padding:0 4px">
        Recettes
    </div>

    <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">

        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #E5E7EB">
            <span style="font-size:14px;color:#6B7280">Versements livreurs</span>
            <span style="font-size:14px;font-weight:700;color:#111827">
                {{ number_format($bilan->versements_livreurs, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #E5E7EB">
            <span style="font-size:14px;color:#6B7280">Factures abonnés payées</span>
            <span style="font-size:14px;font-weight:700;color:#111827">
                {{ number_format($bilan->factures_abonnes, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #E5E7EB">
            <span style="font-size:14px;color:#6B7280">Ventes Boutique</span>
            <span style="font-size:14px;font-weight:700;color:#111827">
                {{ number_format($bilan->ventes_depot, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #E5E7EB">
            <span style="font-size:14px;color:#6B7280">Ventes de pain restant</span>
            <span style="font-size:14px;font-weight:700;color:#111827">
                {{ number_format($bilan->ventes_restant, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:#F0FDF4">
            <span style="font-size:14px;font-weight:700;color:#065F46">Total recettes</span>
            <span style="font-size:15px;font-weight:800;color:#059669">
                {{ number_format($bilan->recettes_total, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>

    </div>

    {{-- ── DÉPENSES ── --}}
    <div style="font-size:11px;font-weight:800;color:#EF4444;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;padding:0 4px">
        Dépenses
    </div>


    <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">

        @if(empty($lignesDepenses))
            <div style="padding:20px 16px;text-align:center;font-size:14px;color:#9CA3AF">
                Aucune dépense ce mois.
            </div>
        @else
            @foreach($lignesDepenses as $ligne)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:13px 16px;border-bottom:1px solid #E5E7EB">
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="font-size:16px">{{ $ligne['icone'] }}</span>
                        <span style="font-size:14px;color:#374151">{{ $ligne['label'] }}</span>
                    </div>
                    <span style="font-size:14px;font-weight:700;color:#111827">
                        {{ number_format($ligne['montant'], 0, ',', ' ') }}&nbsp;FCFA
                    </span>
                </div>
            @endforeach
        @endif

        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:#FEF2F2">
            <span style="font-size:14px;font-weight:700;color:#991B1B">Total dépenses</span>
            <span style="font-size:15px;font-weight:800;color:#EF4444">
                {{ number_format($bilan->depenses_total, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>

    </div>

    @include('bilan._benefice', ['infoStock' => $infoStock, 'benefice' => $benefice])

    {{-- ── Actions ── --}}
    <a href="{{ route('bilan.imprimer.saved', $bilan) }}" target="_blank"
       style="display:flex;align-items:center;justify-content:center;gap:8px;padding:15px;border-radius:14px;font-size:15px;font-weight:700;text-decoration:none;border:2px solid #F97316;color:#F97316;background:#FFFFFF">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Télécharger PDF
    </a>

@endsection
