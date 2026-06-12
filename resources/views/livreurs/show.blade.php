@extends('layouts.app')

@section('title', $livreur->prenom . ' ' . $livreur->nom)

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('livreurs.index') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Détail livreur</span>
        <div style="width:36px"></div>
    </div>

    {{-- ── Profil (conservé tel quel) ── --}}
    <div class="card" style="text-align:center;padding:24px 16px 20px">
        <div class="avatar" style="width:60px;height:60px;font-size:24px;margin:0 auto 10px">
            {{ strtoupper(substr($livreur->prenom, 0, 1)) }}
        </div>
        <div style="font-size:18px;font-weight:700">{{ $livreur->prenom }} {{ $livreur->nom }}</div>
        <div style="font-size:14px;color:var(--text2);margin-top:2px">{{ $livreur->telephone }}</div>
        <div style="margin-top:10px;display:flex;justify-content:center;align-items:center;gap:12px">
            <span class="badge {{ $livreur->actif ? 'badge-green' : 'badge-orange' }}">
                {{ $livreur->actif ? 'Actif' : 'Inactif' }}
            </span>
            <a href="{{ route('livreurs.edit', $livreur) }}" style="font-size:13px;color:var(--orange)">Modifier</a>
            @if($livreur->actif)
                <form method="POST" action="{{ route('livreurs.destroy', $livreur) }}" style="display:inline"
                      onsubmit="return confirm('Désactiver ce livreur ?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="font-size:13px;color:var(--red);border:none;background:none;cursor:pointer;padding:0">
                        Désactiver
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('livreurs.reactiver', $livreur) }}" style="display:inline">
                    @csrf @method('PATCH')
                    <button type="submit" style="font-size:13px;color:var(--green);border:none;background:none;cursor:pointer;padding:0">
                        Réactiver
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- ── Statistiques de la dernière distribution ── --}}
    @php
        $derniereDist      = $livreur->distributions->sortByDesc('date_distribution')->first();
        $aVersement        = $derniereDist?->versement !== null;
        $painsAujd         = $derniereDist ? $derniereDist->pains_attribues : 0;
        $montantAttendu    = $derniereDist ? (float) $derniereDist->montant_attendu : 0;
        $statutAujd        = $derniereDist?->statut ?? 'en_attente';
        $regleeSurplus     = !$aVersement && ($statutAujd === 'reglee');
    @endphp

    <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">

        @if($derniereDist)
            <div style="padding:14px 16px 0;font-size:12px;color:#9CA3AF;font-weight:500">
                Dernière distribution — {{ $derniereDist->date_distribution->format('d/m/Y') }}
            </div>
        @endif

        {{-- Pains attribués --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #E5E7EB">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:9px;height:9px;border-radius:50%;background:#3B82F6;flex-shrink:0"></div>
                <span style="font-size:14px;color:#374151">Pains attribués</span>
            </div>
            <div style="text-align:right">
                <span style="font-size:14px;font-weight:700;color:#111827">{{ $painsAujd }}&nbsp;pains</span>
                @if($derniereDist?->prix_pain)
                    <div style="font-size:12px;color:#9CA3AF;margin-top:1px">{{ number_format($derniereDist->prix_pain, 0, ',', ' ') }}&nbsp;FCFA/pain</div>
                @endif
            </div>
        </div>

        {{-- Pains vendus --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #E5E7EB">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:9px;height:9px;border-radius:50%;background:#10B981;flex-shrink:0"></div>
                <span style="font-size:14px;color:#374151">Pains vendus</span>
            </div>
            @if($aVersement)
                <span style="font-size:14px;font-weight:700;color:#10B981">{{ $derniereDist->pains_vendus }}&nbsp;pains</span>
            @elseif($regleeSurplus)
                <span style="font-size:13px;color:#9CA3AF">—</span>
            @else
                <span style="font-size:13px;font-style:italic;color:#9CA3AF">En attente</span>
            @endif
        </div>

        {{-- Invendus --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #E5E7EB">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:9px;height:9px;border-radius:50%;background:#F97316;flex-shrink:0"></div>
                <span style="font-size:14px;color:#374151">Invendus</span>
            </div>
            @if($aVersement)
                <span style="font-size:14px;font-weight:700;color:#F97316">{{ $derniereDist->invendus }}&nbsp;pains</span>
            @elseif($regleeSurplus)
                <span style="font-size:13px;color:#9CA3AF">—</span>
            @else
                <span style="font-size:13px;font-style:italic;color:#9CA3AF">En attente</span>
            @endif
        </div>

        {{-- Montant attendu --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #E5E7EB">
            <span style="font-size:14px;color:#374151">Montant attendu</span>
            <span style="font-size:14px;font-weight:700;color:#111827">
                {{ number_format($montantAttendu, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>

        {{-- Montant versé --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #E5E7EB">
            <span style="font-size:14px;color:#374151">Montant versé</span>
            @if($aVersement)
                <span style="font-size:14px;font-weight:700;color:#111827">
                    {{ number_format($derniereDist->versement->montant_verse, 0, ',', ' ') }}&nbsp;FCFA
                </span>
            @elseif($regleeSurplus)
                <span style="font-size:13px;color:#9CA3AF">—</span>
            @else
                <span style="font-size:13px;font-style:italic;color:#9CA3AF">En attente</span>
            @endif
        </div>

        {{-- Reliquat du jour --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #E5E7EB;background:#FEF2F2;border-top:1px solid #FECACA">
            <span style="font-size:14px;font-weight:600;color:#EF4444">Reliquat du jour</span>
            @if($aVersement)
                <span style="font-size:14px;font-weight:700;color:#EF4444">
                    {{ number_format((float) $derniereDist->reliquat, 0, ',', ' ') }}&nbsp;FCFA
                </span>
            @elseif($regleeSurplus)
                <span style="font-size:14px;font-weight:700;color:#10B981">0&nbsp;FCFA</span>
            @else
                <span style="font-size:13px;font-style:italic;color:#EF4444">En attente</span>
            @endif
        </div>

        {{-- Statut --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px">
            <span style="font-size:14px;color:#374151">Statut</span>
            @php $estRegleeCarte = $aVersement || $statutAujd === 'reglee'; @endphp
            <span style="font-size:13px;font-weight:700;padding:4px 12px;border-radius:20px;
                {{ $estRegleeCarte
                    ? 'background:#ECFDF5;color:#059669'
                    : 'background:#FFFBEB;color:#D97706' }}">
                {{ $estRegleeCarte ? 'Réglée' : 'En attente' }}
            </span>
        </div>

    </div>

    {{-- ── Boutons d'action ── --}}
    @if($livreur->actif)
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px">
        <a href="{{ route('livreurs.attribuer.form', $livreur) }}"
           style="display:flex;align-items:center;justify-content:center;gap:8px;background:transparent;color:#F97316;border:2px solid #F97316;border-radius:14px;padding:15px 20px;font-size:16px;font-weight:700;text-decoration:none;box-sizing:border-box">
            + Attribuer des pains
        </a>
        <a href="{{ route('livreurs.verser.form', $livreur) }}"
           style="display:flex;align-items:center;justify-content:center;gap:8px;background:#F97316;color:#FFFFFF;border-radius:14px;padding:15px 20px;font-size:16px;font-weight:700;text-decoration:none;{{ $distributionsNonReglees->isEmpty() ? 'opacity:.5;pointer-events:none' : '' }}">
            💰 Enregistrer versement
        </a>
    </div>
    @endif

    {{-- ── Historique distributions (aperçu) ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
        <span style="font-size:16px;font-weight:700;color:#111827">Historique distributions</span>
        <a href="{{ route('livreurs.distributions', $livreur) }}"
           style="font-size:13px;font-weight:600;color:#F97316;text-decoration:none">Voir plus</a>
    </div>

    @php $apercuDist = $livreur->distributions->sortByDesc('date_distribution')->take(3); @endphp

    @if($apercuDist->isEmpty())
        <div style="text-align:center;padding:24px 0;color:#9CA3AF;font-size:14px">
            Aucune distribution enregistrée.
        </div>
    @else
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">
            @foreach($apercuDist as $dist)
                <div style="padding:13px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB' }}">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                        <span style="font-size:14px;font-weight:700;color:#111827">
                            {{ $dist->date_distribution->format('d/m/Y') }}
                        </span>
                        @php $distEstReglee = $dist->versement !== null || $dist->statut === 'reglee'; @endphp
                        <span style="font-size:12px;font-weight:600;padding:3px 9px;border-radius:20px;
                            {{ $distEstReglee
                                ? 'background:#ECFDF5;color:#059669'
                                : 'background:#FFFBEB;color:#D97706' }}">
                            {{ $distEstReglee ? 'Réglée' : 'En attente' }}
                        </span>
                    </div>
                    <span style="font-size:13px;color:#6B7280">
                        {{ $dist->pains_attribues }}&nbsp;pains
                        @if($dist->versement)
                            · Versé&nbsp;: {{ number_format($dist->versement->montant_verse, 0, ',', ' ') }}&nbsp;FCFA
                            @if((float) $dist->reliquat > 0)
                                · <span style="color:#EF4444;font-weight:600">Reliquat&nbsp;: {{ number_format($dist->reliquat, 0, ',', ' ') }}&nbsp;FCFA</span>
                            @endif
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @endif

@endsection
