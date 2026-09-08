@extends('layouts.app')

@section('title', 'Production')

@section('content')

    @php $derniere = $productions->first(); @endphp

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('dashboard') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Production du jour</span>
        <a href="{{ route('productions.create') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none;font-size:24px;font-weight:300;line-height:1">
            +
        </a>
    </div>

    @if(!$derniere)

        {{-- ── Aucune production ── --}}
        <div style="text-align:center;padding:48px 0 32px;color:#9CA3AF;font-size:14px">
            <div style="font-size:42px;margin-bottom:12px">🍞</div>
            Aucune production enregistrée.
        </div>

    @else

        {{-- ── Carte dernière production ── --}}
        <div style="background:#FFFFFF;border-radius:18px;padding:18px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">

            {{-- Date + menu ··· --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid #E5E7EB">
                <div style="font-size:12px;color:#9CA3AF;font-weight:500">
                    Dernière production — {{ $derniere->produit->nom ?? '—' }} · {{ $derniere->date_production->format('d/m/Y') }}
                    <span style="color:#F97316;font-weight:600">· {{ $derniere->moment_icon }} {{ $derniere->moment }} ({{ $derniere->heure_enregistrement }})</span>
                </div>
                <details style="position:relative;flex-shrink:0">
                    <summary style="list-style:none;cursor:pointer;color:#9CA3AF;padding:4px;font-size:20px;font-weight:700;letter-spacing:2px;line-height:1;user-select:none">
                        ···
                    </summary>
                    <div style="position:absolute;right:0;top:28px;background:#FFFFFF;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.14);z-index:50;min-width:148px;overflow:hidden;border:1px solid #F3F4F6">
                        <a href="{{ route('productions.edit', $derniere) }}"
                           style="display:flex;align-items:center;gap:10px;padding:13px 16px;font-size:14px;font-weight:500;color:#111827;text-decoration:none">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier
                        </a>
                        <div style="height:1px;background:#F3F4F6"></div>
                        <form method="POST" action="{{ route('productions.destroy', $derniere) }}"
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

            {{-- Farine utilisée --}}
            <div style="display:flex;align-items:center;gap:14px;padding:11px 0;border-bottom:1px solid #E5E7EB">
                <div style="width:42px;height:42px;border-radius:11px;background:#FFF3E0;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
                    🌾
                </div>
                <div style="flex:1">
                    <span style="font-size:14px;font-weight:600;color:#F97316">Farine utilisée</span>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:20px;font-weight:800;color:#111827;line-height:1">{{ \App\Support\Nombre::qte($derniere->nombre_sacs) }}</div>
                    <div style="font-size:11px;color:#9CA3AF;margin-top:2px">sacs</div>
                </div>
            </div>

            {{-- Levure utilisée --}}
            <div style="display:flex;align-items:center;gap:14px;padding:11px 0;border-bottom:1px solid #E5E7EB">
                <div style="width:42px;height:42px;border-radius:11px;background:#FFF0F0;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
                    🧪
                </div>
                <div style="flex:1">
                    <span style="font-size:14px;font-weight:600;color:#F97316">Levure utilisée</span>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:20px;font-weight:800;color:#111827;line-height:1">{{ \App\Support\Nombre::qte($derniere->quantite_levure) }}</div>
                    <div style="font-size:11px;color:#9CA3AF;margin-top:2px">paquet{{ $derniere->quantite_levure > 1 ? 's' : '' }}</div>
                </div>
            </div>

            {{-- Pains produits --}}
            <div style="display:flex;align-items:center;gap:14px;padding:11px 0">
                <div style="width:42px;height:42px;border-radius:11px;background:#ECFDF5;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
                    🍞
                </div>
                <div style="flex:1">
                    <span style="font-size:14px;font-weight:600;color:#F97316">Pains produits</span>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:20px;font-weight:800;color:#111827;line-height:1">{{ number_format($derniere->nombre_pains_produits, 0, ',', ' ') }}</div>
                    <div style="font-size:11px;color:#9CA3AF;margin-top:2px">pains</div>
                </div>
            </div>

        </div>

    @endif

    {{-- ── Historique production ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
        <span style="font-size:16px;font-weight:700;color:#111827">Historique production</span>
        <a href="{{ route('productions.historique') }}"
           style="font-size:13px;font-weight:600;color:#F97316;text-decoration:none">
            Voir plus
        </a>
    </div>

    @php $apercu = $productions->skip(1)->take(4); @endphp

    @if($apercu->isEmpty() && !$derniere)
        <div style="text-align:center;padding:24px 0;color:#9CA3AF;font-size:14px">
            Aucun historique disponible.
        </div>
    @elseif($apercu->isEmpty())
        <div style="text-align:center;padding:16px 0;color:#9CA3AF;font-size:13px">
            Pas d'autres productions.
        </div>
    @else
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">
            @foreach($apercu as $prod)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">
                    <span style="font-size:14px;font-weight:600;color:#111827">
                        {{ $prod->produit->nom ?? '—' }} · {{ $prod->date_production->format('d/m/Y') }}
                        <span style="font-size:12px;font-weight:500;color:#9CA3AF">{{ $prod->moment_icon }} {{ $prod->heure_enregistrement }}</span>
                    </span>
                    <span style="font-size:13px;color:#6B7280">
                        {{ \App\Support\Nombre::qte($prod->nombre_sacs) }} sac{{ $prod->nombre_sacs > 1 ? 's' : '' }}
                        &nbsp;–&nbsp;
                        {{ number_format($prod->nombre_pains_produits, 0, ',', ' ') }} pains
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Bouton Ajouter production ── --}}
    <a href="{{ route('productions.create') }}"
       style="display:flex;align-items:center;justify-content:center;gap:8px;background:#F97316;color:#FFFFFF;border-radius:14px;padding:16px 20px;font-size:16px;font-weight:700;text-decoration:none;width:100%;box-sizing:border-box">
        + Ajouter production
    </a>

@endsection
