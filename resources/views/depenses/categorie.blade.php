@extends('layouts.app')

@section('title', $libelle)

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;margin-bottom:22px">
        <div style="flex:1">
            <a href="{{ route('depenses.index', ['du' => $du, 'au' => $au]) }}"
               style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
        <span style="font-size:17px;font-weight:700;color:#111827">{{ $libelle }}</span>
        <div style="flex:1;display:flex;justify-content:flex-end">
            @unless($estAutomatique)
                <a href="{{ route('depenses.create') }}"
                   style="background:#F97316;color:#FFFFFF;border-radius:20px;padding:8px 16px;font-size:14px;font-weight:700;text-decoration:none">
                    Ajouter
                </a>
            @endunless
        </div>
    </div>

    {{-- ── Période ── --}}
    @include('partials.periode', ['action' => route('depenses.categorie', $categorie)])

    @if($depenses->isEmpty())

        <div style="text-align:center;padding:48px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">💸</div>
            Aucune dépense sur cette période.
        </div>

    @else

        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">
            @foreach($depenses as $depense)
                {{-- Achats du Stock : la ligne suffit (pas de fiche de détail). Autres dépenses : la fiche permet de modifier/supprimer. --}}
                <{!! $estAutomatique ? 'div' : 'a href="'.e(route('depenses.show', $depense)).'"' !!}
                   style="display:flex;align-items:center;gap:12px;padding:14px 16px;text-decoration:none;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $depense->libelle }}</div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px">{{ $depense->date_depense->format('d/m/Y') }}</div>
                    </div>
                    <div style="font-size:14px;font-weight:700;color:#111827;flex-shrink:0">
                        {{ number_format($depense->montant, 0, ',', ' ') }}&nbsp;FCFA
                    </div>
                    @unless($estAutomatique)
                        <svg width="16" height="16" fill="none" stroke="#9CA3AF" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    @endunless
                </{{ $estAutomatique ? 'div' : 'a' }}>
            @endforeach
        </div>

        @include('partials.pagination', ['paginator' => $depenses])

        {{-- ── Total de la catégorie sur la période ── --}}
        <div style="background:#F97316;border-radius:14px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:15px;font-weight:600;color:#FFFFFF">Total {{ mb_strtolower($libelle) }}</div>
                <div style="font-size:12px;color:rgba(255,255,255,.8);margin-top:2px">
                    {{ $nombre }} dépense{{ $nombre > 1 ? 's' : '' }} · du {{ \Carbon\Carbon::parse($du)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($au)->format('d/m/Y') }}
                </div>
            </div>
            <span style="font-size:16px;font-weight:800;color:#FFFFFF">{{ number_format($total, 0, ',', ' ') }}&nbsp;FCFA</span>
        </div>

    @endif

@endsection
