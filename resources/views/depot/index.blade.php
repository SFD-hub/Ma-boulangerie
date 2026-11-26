@extends('layouts.app')

@section('title', 'Boutique')

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
        <span style="font-size:17px;font-weight:700;color:#111827">Boutique</span>
        <div style="flex:1;display:flex;justify-content:flex-end">
            <a href="{{ route('clients.create', ['type' => 'vendeur']) }}"
               style="background:#F97316;color:#FFFFFF;border-radius:20px;padding:8px 16px;font-size:14px;font-weight:700;text-decoration:none">
                Ajouter
            </a>
        </div>
    </div>

    {{-- Choix de la date --}}
    <div style="margin-bottom:16px">
        <input type="date" value="{{ $date }}" max="{{ date('Y-m-d') }}"
               onchange="window.location = '{{ route('depot.index') }}?date=' + this.value"
               style="padding:8px 12px;border-radius:10px;border:1px solid var(--border);font-size:13px;color:#111827;background:#FFFFFF">
    </div>

    {{-- ── Totaux du jour ── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px">
        <div style="background:#D1FAE5;border-radius:18px;padding:18px 14px">
            <div style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:10px;line-height:1.3">
                Pains vendus
            </div>
            <div style="font-size:34px;font-weight:800;color:#111827;line-height:1">
                {{ \App\Support\Nombre::painsEntiers($totalQuantite) }}
            </div>
            <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">{{ \App\Support\Nombre::painsLegende($totalQuantite) }}</div>
        </div>

        <div style="background:#DBEAFE;border-radius:18px;padding:18px 14px">
            <div style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:10px;line-height:1.3">
                Argent encaissé
            </div>
            <div style="font-size:34px;font-weight:800;color:#111827;line-height:1">
                {{ number_format($totalMontant, 0, ',', ' ') }}
            </div>
            <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">FCFA</div>
        </div>
    </div>

    {{-- ── Vendeurs : un par carte, chacun avec ses pains et son reliquat ── --}}
    <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:10px">Vendeurs</div>
    @if($vendeurs->isEmpty())
        <div style="background:#FFFFFF;border-radius:16px;padding:22px 16px;text-align:center;font-size:13px;color:#9CA3AF;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:22px">
            Aucun vendeur. Touchez « Ajouter » pour en créer un.
        </div>
    @else
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:22px">
            @foreach($vendeurs as $v)
                @php
                    $pains = (float) ($painsDuJour[$v->id] ?? 0);
                    $reste = (float) ($resteAEncaisser[$v->id] ?? 0);
                @endphp
                <a href="{{ route('livreurs.show', $v) }}"
                   style="display:flex;align-items:center;gap:12px;padding:14px 16px;text-decoration:none;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">
                    <div class="avatar">{{ strtoupper(substr($v->prenom ?: $v->nom, 0, 1)) }}</div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:700;color:#111827">{{ trim($v->prenom . ' ' . $v->nom) }}</div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px">
                            {{ $pains > 0 ? \App\Support\Nombre::pains($pains) . ' reçu' . ($pains > 1 ? 's' : '') . ' ce jour' : 'Aucun pain ce jour' }}
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        @if($reste > 0)
                            <div style="font-size:14px;font-weight:800;color:#D97706">{{ number_format($reste, 0, ',', ' ') }}&nbsp;FCFA</div>
                            <div style="font-size:12px;color:#9CA3AF;margin-top:2px">à encaisser</div>
                        @else
                            <div style="font-size:12px;font-weight:600;color:#059669">À jour</div>
                        @endif
                    </div>
                    <svg width="16" height="16" fill="none" stroke="#9CA3AF" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endforeach
        </div>
    @endif

    {{-- ── Anciennes ventes saisies au comptoir ce jour-là (déjà comptées dans les totaux) ── --}}
    @if($ventes->isNotEmpty())
        <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:10px">Ventes au comptoir</div>
        <div class="card">
            @foreach($ventes as $vente)
                <div class="list-item">
                    <div class="avatar">🏪</div>
                    <div class="list-item-body">
                        <div class="list-item-name">{{ $vente->produit->nom ?? 'Pain' }}</div>
                        <div class="list-item-sub">{{ $vente->descriptionPieces() }}</div>
                    </div>
                    <div class="list-item-right" style="display:flex;align-items:center;gap:8px">
                        <span style="font-size:14px;font-weight:700;color:#111827">
                            {{ number_format($vente->montant, 0, ',', ' ') }}&nbsp;FCFA
                        </span>
                        <details style="position:relative;flex-shrink:0">
                            <summary style="list-style:none;cursor:pointer;color:#9CA3AF;padding:4px;font-size:20px;font-weight:700;letter-spacing:2px;line-height:1;user-select:none">
                                ···
                            </summary>
                            <div style="position:absolute;right:0;top:28px;background:#FFFFFF;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.14);z-index:50;min-width:148px;overflow:hidden;border:1px solid #F3F4F6">
                                <form method="POST" action="{{ route('depot.destroy', $vente) }}"
                                      onsubmit="return confirm('Supprimer cette vente ?')">
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
                </div>
            @endforeach
        </div>
    @endif

@endsection
