@extends('layouts.app')

@section('title', 'Ventes de sacs')

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;margin-bottom:22px">
        <div style="flex:1">
            <a href="{{ route('pain-restant.index', ['du' => $du, 'au' => $au]) }}"
               style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
        <span style="font-size:17px;font-weight:700;color:#111827">Ventes de sacs</span>
        <div style="flex:1"></div>
    </div>

    {{-- ── Période ── --}}
    @include('partials.periode', ['action' => route('pain-restant.historique')])

    @if($liste->isEmpty())

        <div style="text-align:center;padding:48px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">🛍️</div>
            Aucune vente sur cette période.
        </div>

    @else

        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">
            @foreach($liste as $vente)
                <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:700;color:#111827">{{ $vente->nombre_sacs }} sac{{ $vente->nombre_sacs > 1 ? 's' : '' }}</div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px">
                            {{ $vente->date_vente->format('d/m/Y') }} · ≈ {{ $vente->pains_estimes }} pain{{ $vente->pains_estimes > 1 ? 's' : '' }}
                        </div>
                    </div>
                    <div style="font-size:14px;font-weight:800;color:#111827;flex-shrink:0">{{ number_format($vente->montant, 0, ',', ' ') }}&nbsp;FCFA</div>
                    <form method="POST" action="{{ route('pain-restant.destroy', $vente) }}" onsubmit="return confirm('Supprimer cette vente ?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="font-size:13px;font-weight:600;color:#EF4444;background:none;border:none;cursor:pointer;padding:6px 4px">Supprimer</button>
                    </form>
                </div>
            @endforeach
        </div>

        @include('partials.pagination', ['paginator' => $liste])

        {{-- ── Total de la période (toutes les pages) ── --}}
        <div style="background:#F97316;border-radius:14px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:15px;font-weight:600;color:#FFFFFF">Total ventes</div>
                <div style="font-size:12px;color:rgba(255,255,255,.8);margin-top:2px">
                    {{ $ventes['sacs'] }} sac{{ $ventes['sacs'] > 1 ? 's' : '' }} · du {{ \Carbon\Carbon::parse($du)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($au)->format('d/m/Y') }}
                </div>
            </div>
            <span style="font-size:16px;font-weight:800;color:#FFFFFF">{{ number_format($ventes['montant'], 0, ',', ' ') }}&nbsp;FCFA</span>
        </div>

    @endif

@endsection
