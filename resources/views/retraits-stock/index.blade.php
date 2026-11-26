@extends('layouts.app')

@section('title', 'Sacs et bidons terminés')

@section('content')

    {{-- ── En-tête ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('matieres-premieres.index') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Terminés</span>
        <div style="width:36px"></div>
    </div>

    {{-- ── Durée moyenne d'une unité ── --}}
    @if($matieres->isNotEmpty())
        <div style="display:grid;grid-template-columns:repeat({{ min(3, $matieres->count()) }},1fr);gap:10px;margin-bottom:18px">
            @foreach($matieres as $mp)
                @php $moy = $moyennes[$mp->id] ?? null; @endphp
                <div style="background:#FFFFFF;border-radius:14px;padding:12px 10px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.07)">
                    <div style="font-size:22px">{{ $mp->icone() }}</div>
                    <div style="font-size:12px;font-weight:600;color:#6B7280;margin:4px 0 2px">{{ $mp->nom }}</div>
                    @if($moy)
                        <div style="font-size:18px;font-weight:800;color:#111827">{{ number_format((float) $moy->jours, 0) }}&nbsp;j</div>
                        <div style="font-size:11px;color:#9CA3AF">en moyenne / {{ $mp->unite }}</div>
                    @else
                        <div style="font-size:16px;font-weight:700;color:#D1D5DB">—</div>
                        <div style="font-size:11px;color:#9CA3AF">pas encore de données</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if($retraits->isEmpty())

        <div style="text-align:center;padding:60px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">🫙</div>
            Aucun sac ou bidon terminé pour l'instant.
        </div>

    @else

        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">
            @foreach($retraits as $retrait)
                @php $duree = $retrait->dureeJours(); @endphp
                <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}position:relative">

                    <div style="width:44px;height:44px;border-radius:12px;background:{{ $retrait->matierePremiere->fond() }};display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">
                        {{ $retrait->matierePremiere->icone() }}
                    </div>

                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:700;color:#111827">
                            {{ ucfirst($retrait->matierePremiere->unite) }} de {{ mb_strtolower($retrait->matierePremiere->nom) }} terminé
                        </div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px">
                            {{ $retrait->date_retrait->format('d/m/Y') }}
                            @if($retrait->user) · {{ $retrait->user->name }} @endif
                            · reste {{ \App\Support\Nombre::qte($retrait->stock_apres) }}
                        </div>
                        @if($retrait->note)
                            <div style="font-size:12px;color:#6B7280;margin-top:2px;font-style:italic">« {{ $retrait->note }} »</div>
                        @endif
                    </div>

                    @if($duree !== null)
                        <div style="font-size:13px;font-weight:700;color:#374151;flex-shrink:0">
                            {{ $duree }}&nbsp;jour{{ $duree > 1 ? 's' : '' }}
                        </div>
                    @endif

                    @if($retrait->peutEtreAnnuleePar(auth()->user()))
                        <form method="POST" action="{{ route('retraits-stock.destroy', $retrait) }}"
                              onsubmit="return confirm('Annuler cette validation ? Le stock sera rétabli.')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Annuler cette validation"
                                    style="background:none;border:none;color:#EF4444;font-size:13px;font-weight:600;cursor:pointer;padding:6px 4px;font-family:inherit">
                                Annuler
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        @if($retraits->hasPages())
            <div style="margin-bottom:16px">{{ $retraits->links() }}</div>
        @endif

    @endif

@endsection
