@extends('layouts.app')

@section('title', 'Historique distributions')

@section('content')

    {{-- ── En-tête ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('livreurs.show', $livreur) }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Historique distributions</span>
        <div style="width:36px"></div>
    </div>

    {{-- Sous-titre livreur --}}
    <div style="font-size:13px;color:#9CA3AF;margin-bottom:16px;text-align:center">
        {{ $livreur->prenom }} {{ $livreur->nom }}
    </div>

    {{-- ── Reliquat total en attente (toujours affiché en haut) ── --}}
    <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:14px;padding:14px 16px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-size:11px;font-weight:700;color:#EF4444;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px">
                Reliquat total en attente
            </div>
            <div style="font-size:12px;color:#9CA3AF">Somme de tous les reliquats non réglés</div>
        </div>
        <span style="font-size:18px;font-weight:800;color:{{ $reliquatTotalEnAttente > 0 ? '#EF4444' : '#10B981' }}">
            {{ number_format($reliquatTotalEnAttente, 0, ',', ' ') }}&nbsp;FCFA
        </span>
    </div>

    @php
        $distributions = $livreur->distributions->sortByDesc(
            fn ($d) => $d->date_distribution->format('Y-m-d') . ' ' . $d->created_at->format('H:i:s')
        );
    @endphp

    @if($distributions->isEmpty())

        <div style="text-align:center;padding:60px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">📦</div>
            Aucune distribution enregistrée.
        </div>

    @else

        {{-- ── Liste complète ── --}}
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">

            @foreach($distributions as $dist)
                @php
                    $estReglee = (float) $dist->reliquat <= 0;
                    $aVersement = $dist->versement !== null;
                @endphp
                <div style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}position:relative;{{ !$estReglee ? 'background:#FFFBEB;border-left:3px solid #F97316;' : '' }}">

                    {{-- Icône statut --}}
                    <div style="width:44px;height:44px;border-radius:11px;background:{{ $estReglee ? '#ECFDF5' : '#FFFBEB' }};display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
                        {{ $estReglee ? '✅' : '🕐' }}
                    </div>

                    {{-- Infos --}}
                    <div style="flex:1;min-width:0">

                        {{-- Date + badge statut --}}
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                            <span style="font-size:14px;font-weight:700;color:#111827">
                                {{ $dist->produit->nom ?? '—' }} · {{ $dist->date_distribution->format('d/m/Y') }}
                            </span>
                            <span style="font-size:11px;font-weight:600;color:#9CA3AF">{{ $dist->moment_icon }} {{ $dist->moment }} · {{ $dist->heure_enregistrement }}</span>
                            <span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px;
                                {{ $estReglee ? 'background:#ECFDF5;color:#059669' : 'background:#FFFBEB;color:#D97706' }}">
                                {{ $estReglee ? 'Réglée' : 'En attente' }}
                            </span>
                        </div>

                        {{-- Pains attribués / vendus / invendus --}}
                        <div style="font-size:13px;color:#6B7280;margin-bottom:3px">
                            <span>{{ $dist->pains_attribues }}&nbsp;attribués</span>
                            @if($dist->prix_pain)
                                · <span style="color:#6B7280">{{ number_format($dist->prix_pain, 0, ',', ' ') }}&nbsp;FCFA/pain</span>
                            @endif
                            @if($aVersement)
                                · <span style="color:#10B981">{{ $dist->pains_vendus }}&nbsp;vendus</span>
                                · <span style="color:#F97316">{{ $dist->invendus }}&nbsp;invendus</span>
                            @elseif(!$estReglee)
                                · <span style="font-style:italic;color:#9CA3AF">ventes en attente</span>
                            @endif
                        </div>

                        {{-- Montants --}}
                        <div style="font-size:13px;color:#6B7280">
                            Attendu&nbsp;: {{ number_format((float) $dist->montant_attendu, 0, ',', ' ') }}&nbsp;FCFA
                            @if($aVersement)
                                · Versé&nbsp;: {{ number_format((float) $dist->versement->montant_verse, 0, ',', ' ') }}&nbsp;FCFA
                            @elseif(!$estReglee)
                                · <span style="font-style:italic">Versement non enregistré</span>
                            @endif
                        </div>

                        {{-- Reliquat (si > 0) --}}
                        @if((float) $dist->reliquat > 0)
                            <div style="margin-top:4px;display:flex;align-items:center;justify-content:space-between;gap:10px">
                                <div style="font-size:13px;font-weight:700;color:#EF4444">
                                    Reliquat&nbsp;: {{ number_format((float) $dist->reliquat, 0, ',', ' ') }}&nbsp;FCFA
                                </div>
                                <a href="{{ route('livreurs.verser.form', $livreur) }}?distribution_id={{ $dist->id }}"
                                   style="flex-shrink:0;font-size:12px;font-weight:700;color:#FFFFFF;background:#F97316;border-radius:20px;padding:6px 14px;text-decoration:none">
                                    Régler
                                </a>
                            </div>
                        @endif

                    </div>

                    {{-- Menu ··· --}}
                    <details style="position:relative;flex-shrink:0">
                        <summary style="list-style:none;cursor:pointer;color:#9CA3AF;padding:8px 4px;font-size:20px;font-weight:700;letter-spacing:2px;line-height:1;user-select:none">
                            ···
                        </summary>
                        <div style="position:absolute;right:0;top:36px;background:#FFFFFF;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.14);z-index:50;min-width:148px;overflow:hidden;border:1px solid #F3F4F6">
                            <a href="{{ route('distributions.edit', $dist) }}"
                               style="display:flex;align-items:center;gap:10px;padding:13px 16px;font-size:14px;font-weight:500;color:#111827;text-decoration:none">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Modifier
                            </a>
                            <div style="height:1px;background:#F3F4F6"></div>
                            <form method="POST" action="{{ route('distributions.destroy', $dist) }}"
                                  onsubmit="return confirm('Supprimer cette distribution ?')">
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

    @endif

@endsection
