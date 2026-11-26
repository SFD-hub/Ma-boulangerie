@extends('layouts.app')

@section('title', 'Stock')

@section('content')

    @php
        $manquants = collect(array_keys(\App\Models\MatierePremiere::CATALOGUE))
            ->diff($matieresPremières->pluck('nom'));

        // Le bouton « Stock initial » n'apparaît que s'il reste un produit sans stock ni historique.
        $stockInitialPossible = $matieresPremières->contains(
            fn ($m) => (float) $m->stock_actuel == 0.0 && $m->achats->isEmpty()
        );
    @endphp

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
        <span style="font-size:17px;font-weight:700;color:#111827">Stock</span>
        <div style="flex:1;display:flex;justify-content:flex-end;align-items:center;gap:4px">
            @if($stockInitialPossible)
                <a href="{{ route('stock-initial.index') }}"
                   style="background:#F97316;color:#FFFFFF;border-radius:20px;padding:8px 16px;font-size:14px;font-weight:700;text-decoration:none">
                    Stock initial
                </a>
            @endif

            {{-- Menu ··· : actions rares (inventaire) --}}
            <details style="position:relative;flex-shrink:0">
                <summary style="list-style:none;cursor:pointer;color:#6B7280;padding:8px 8px;font-size:20px;font-weight:700;letter-spacing:2px;line-height:1;user-select:none">
                    ···
                </summary>
                <div style="position:absolute;right:0;top:40px;background:#FFFFFF;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.14);z-index:50;min-width:160px;overflow:hidden;border:1px solid #F3F4F6">
                    <a href="{{ route('inventaire.index') }}"
                       style="display:flex;align-items:center;gap:10px;padding:13px 16px;font-size:14px;font-weight:500;color:#111827;text-decoration:none">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Inventaire
                    </a>
                </div>
            </details>
        </div>
    </div>

    {{-- ── Alerte configuration manquante ── --}}
    @if($manquants->isNotEmpty())
        <div class="alert-strip" style="margin-bottom:16px">
            ⚠️ Produits de stock non configurés : {{ $manquants->implode(', ') }}.
        </div>
    @endif

    {{-- ── Cartes stock : chaque carte ouvre le détail du produit ── --}}
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:26px">

        @foreach($matieresPremières as $mp)
            @php
                $stock    = (float) $mp->stock_actuel;
                $enAlerte = $stock <= (float) $mp->seuil_alerte && (float) $mp->seuil_alerte > 0;
            @endphp
            <a href="{{ route('matieres-premieres.show', $mp) }}"
               style="display:block;text-decoration:none;color:inherit;background:#FFFFFF;border-radius:16px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid {{ $enAlerte ? '#FECACA' : '#F3F4F6' }}">
                <div style="display:flex;align-items:center;gap:14px">
                    <div style="width:54px;height:54px;border-radius:14px;background:{{ $mp->fond() }};display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0">
                        {{ $mp->icone() }}
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:15px;font-weight:700;color:#111827">{{ $mp->nom }}</div>
                        <div style="font-size:13px;color:{{ $enAlerte ? '#EF4444' : '#9CA3AF' }};margin-top:2px">
                            {{ $enAlerte ? 'Stock faible' : 'Stock disponible' }}
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-size:30px;font-weight:800;line-height:1;color:{{ $enAlerte ? '#EF4444' : '#111827' }}">
                            {{ \App\Support\Nombre::qte($stock) }}
                        </div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:4px">{{ $mp->uniteLibelle($stock) }}</div>
                    </div>
                    <svg width="18" height="18" fill="none" stroke="#D1D5DB" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        @endforeach

    </div>

@endsection
