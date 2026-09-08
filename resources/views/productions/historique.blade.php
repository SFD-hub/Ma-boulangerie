@extends('layouts.app')

@section('title', 'Historique des productions')

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('productions.index') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Historique des productions</span>
        <a href="{{ route('productions.create') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#9CA3AF;text-decoration:none">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
        </a>
    </div>

    @if($productions->isEmpty())

        <div style="text-align:center;padding:60px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">🍞</div>
            Aucune production enregistrée.
        </div>

    @else

        {{-- ── Liste des productions ── --}}
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">

            @foreach($productions as $prod)
                <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }};position:relative">

                    {{-- Icône --}}
                    <div style="width:48px;height:48px;border-radius:12px;background:#ECFDF5;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">
                        🍞
                    </div>

                    {{-- Infos --}}
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:700;color:#111827">
                            {{ $prod->produit->nom ?? '—' }} · {{ $prod->date_production->format('d/m/Y') }}
                            <span style="font-size:12px;font-weight:600;color:#9CA3AF">{{ $prod->moment_icon }} {{ $prod->moment }} · {{ $prod->heure_enregistrement }}</span>
                        </div>
                        <div style="font-size:13px;color:#6B7280;margin-top:2px">
                            {{ \App\Support\Nombre::qte($prod->nombre_sacs) }}&nbsp;sac{{ $prod->nombre_sacs > 1 ? 's' : '' }} farine
                            &nbsp;·&nbsp;
                            {{ \App\Support\Nombre::qte($prod->quantite_levure) }}&nbsp;paquet{{ $prod->quantite_levure > 1 ? 's' : '' }} levure
                        </div>
                        <div style="font-size:13px;font-weight:700;color:#F97316;margin-top:2px">
                            {{ number_format($prod->nombre_pains_produits, 0, ',', ' ') }} pains
                        </div>
                    </div>

                    {{-- Menu ··· --}}
                    <details style="position:relative;flex-shrink:0">
                        <summary style="list-style:none;cursor:pointer;color:#9CA3AF;padding:8px 4px;font-size:20px;font-weight:700;letter-spacing:2px;line-height:1;user-select:none">
                            ···
                        </summary>
                        <div style="position:absolute;right:0;top:36px;background:#FFFFFF;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.14);z-index:50;min-width:148px;overflow:hidden;border:1px solid #F3F4F6">
                            <a href="{{ route('productions.edit', $prod) }}"
                               style="display:flex;align-items:center;gap:10px;padding:13px 16px;font-size:14px;font-weight:500;color:#111827;text-decoration:none">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Modifier
                            </a>
                            <div style="height:1px;background:#F3F4F6"></div>
                            <form method="POST" action="{{ route('productions.destroy', $prod) }}"
                                  onsubmit="return confirm('Supprimer cette production ? Le stock sera restauré.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        style="display:flex;align-items:center;gap:10px;padding:13px 16px;font-size:14px;font-weight:500;color:#EF4444;background:none;border:none;cursor:pointer;width:100%;text-align:left">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </details>

                </div>
            @endforeach

        </div>

        {{-- ── Pagination ── --}}
        @if($productions->hasPages())
            <div style="display:flex;align-items:center;justify-content:space-between;padding:4px 0 8px">

                @if($productions->onFirstPage())
                    <span style="font-size:14px;font-weight:600;color:#D1D5DB;padding:10px 16px;background:#F9FAFB;border-radius:10px">
                        ← Précédent
                    </span>
                @else
                    <a href="{{ $productions->previousPageUrl() }}"
                       style="font-size:14px;font-weight:600;color:#F97316;padding:10px 16px;background:#FFF7ED;border-radius:10px;text-decoration:none">
                        ← Précédent
                    </a>
                @endif

                <span style="font-size:13px;color:#9CA3AF;font-weight:500">
                    Page {{ $productions->currentPage() }} / {{ $productions->lastPage() }}
                </span>

                @if($productions->hasMorePages())
                    <a href="{{ $productions->nextPageUrl() }}"
                       style="font-size:14px;font-weight:600;color:#F97316;padding:10px 16px;background:#FFF7ED;border-radius:10px;text-decoration:none">
                        Suivant →
                    </a>
                @else
                    <span style="font-size:14px;font-weight:600;color:#D1D5DB;padding:10px 16px;background:#F9FAFB;border-radius:10px">
                        Suivant →
                    </span>
                @endif

            </div>
        @endif

        {{-- ── Total pains ── --}}
        <div style="background:#F97316;border-radius:14px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:15px;font-weight:600;color:#FFFFFF">Total pains produits</span>
            <span style="font-size:16px;font-weight:800;color:#FFFFFF">{{ number_format($totalPains, 0, ',', ' ') }}</span>
        </div>

    @endif

@endsection
