@extends('layouts.app')

@section('title', 'Statistiques')

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('dashboard') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Statistiques</span>
        <div style="width:36px"></div>
    </div>

    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- 1. PRODUCTION TOTALE                                     --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div style="font-size:11px;font-weight:800;color:#F97316;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;padding:0 4px">
        Production totale
    </div>

    <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:12px">

        @php
            $lignesProd = [
                ['label' => 'Pains produits',    'valeur' => $totalPainsProduits,   'couleur' => '#F97316'],
                ['label' => 'Pains distribués',   'valeur' => $totalPainsDistribues, 'couleur' => '#3B82F6'],
                ['label' => 'Pains vendus',       'valeur' => $totalPainsVendus,     'couleur' => '#10B981'],
                ['label' => 'Invendus',           'valeur' => $totalInvendus,        'couleur' => '#EF4444'],
            ];
        @endphp

        @foreach($lignesProd as $i => $ligne)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;{{ $i < count($lignesProd) - 1 ? 'border-bottom:1px solid #E5E7EB' : '' }}">
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:10px;height:10px;border-radius:50%;background:{{ $ligne['couleur'] }};flex-shrink:0"></div>
                    <span style="font-size:14px;color:#374151">{{ $ligne['label'] }}</span>
                </div>
                <span style="font-size:15px;font-weight:800;color:{{ $ligne['couleur'] }}">
                    {{ number_format($ligne['valeur'], 0, ',', ' ') }}
                    <span style="font-size:12px;font-weight:500;color:#9CA3AF">pains</span>
                </span>
            </div>
        @endforeach

    </div>

    {{-- Taux de vente --}}
    @php
        $tauxCouleur = $tauxVente >= 80 ? '#059669' : ($tauxVente >= 60 ? '#D97706' : '#EF4444');
        $tauxBg      = $tauxVente >= 80 ? '#ECFDF5' : ($tauxVente >= 60 ? '#FFFBEB' : '#FEF2F2');
        $tauxBorder  = $tauxVente >= 80 ? '#A7F3D0' : ($tauxVente >= 60 ? '#FCD34D' : '#FCA5A5');
    @endphp
    <div style="background:{{ $tauxBg }};border:1.5px solid {{ $tauxBorder }};border-radius:14px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-size:12px;font-weight:700;color:{{ $tauxCouleur }};text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px">
                Taux de vente
            </div>
            <div style="font-size:11px;color:#9CA3AF">
                {{ number_format($totalPainsVendus, 0, ',', ' ') }} vendus / {{ number_format($totalPainsDistribues, 0, ',', ' ') }} distribués
            </div>
        </div>
        <div style="font-size:36px;font-weight:900;color:{{ $tauxCouleur }};line-height:1">
            {{ $tauxVente }}<span style="font-size:18px">%</span>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- 2. FINANCES RAPIDES (mois courant)                       --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div style="font-size:11px;font-weight:800;color:#6B7280;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;padding:0 4px">
        Finances — {{ $moisNomCourant }}
    </div>

    <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:24px">

        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #E5E7EB">
            <span style="font-size:14px;color:#6B7280">Recettes du mois</span>
            <span style="font-size:14px;font-weight:700;color:#059669">
                {{ number_format($recettesMois, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #E5E7EB">
            <span style="font-size:14px;color:#6B7280">Dépenses du mois</span>
            <span style="font-size:14px;font-weight:700;color:#EF4444">
                {{ number_format($depensesMois, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:{{ $resultatMois > 0 ? '#F0FDF4' : ($resultatMois < 0 ? '#FEF2F2' : '#F9FAFB') }}">
            <span style="font-size:14px;font-weight:700;color:{{ $resultatMois > 0 ? '#065F46' : ($resultatMois < 0 ? '#991B1B' : '#374151') }}">
                {{ $resultatMois > 0 ? '🟢 Bénéfice' : ($resultatMois < 0 ? '🔴 Perte' : '⚪ Équilibre') }}
            </span>
            <span style="font-size:15px;font-weight:800;color:{{ $resultatMois > 0 ? '#059669' : ($resultatMois < 0 ? '#EF4444' : '#6B7280') }}">
                {{ $resultatMois > 0 ? '+' : '' }}{{ number_format($resultatMois, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>

    </div>

    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- 3. MEILLEUR LIVREUR DU MOIS                             --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div style="font-size:11px;font-weight:800;color:#6B7280;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;padding:0 4px">
        Meilleur livreur — {{ $moisNomCourant }}
    </div>

    @if(!$meilleursLivreur || $meilleursLivreur['pains_vendus'] === 0)
        <div style="background:#FFFFFF;border-radius:18px;padding:24px 20px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:24px;color:#9CA3AF;font-size:14px">
            Aucune vente enregistrée ce mois.
        </div>
    @else
        @php $ml = $meilleursLivreur; @endphp
        <div style="background:#FFFFFF;border-radius:18px;padding:20px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:24px">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px">
                <div style="width:52px;height:52px;border-radius:16px;background:#FFF7ED;display:flex;align-items:center;justify-content:center;font-size:26px;flex-shrink:0">
                    🏆
                </div>
                <div>
                    <div style="font-size:17px;font-weight:800;color:#111827">
                        {{ $ml['livreur']->prenom }} {{ $ml['livreur']->nom }}
                    </div>
                    <div style="font-size:12px;color:#9CA3AF;margin-top:2px">Meilleur du mois</div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div style="background:#FFF7ED;border-radius:12px;padding:12px;text-align:center">
                    <div style="font-size:22px;font-weight:800;color:#F97316">{{ number_format($ml['pains_vendus'], 0, ',', ' ') }}</div>
                    <div style="font-size:12px;color:#9CA3AF;margin-top:2px">pains vendus</div>
                </div>
                <div style="background:#ECFDF5;border-radius:12px;padding:12px;text-align:center">
                    <div style="font-size:16px;font-weight:800;color:#059669">{{ number_format($ml['montant_encaisse'], 0, ',', ' ') }}</div>
                    <div style="font-size:12px;color:#9CA3AF;margin-top:2px">FCFA encaissés</div>
                </div>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- 4. LIVREUR À SURVEILLER                                 --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div style="font-size:11px;font-weight:800;color:#EF4444;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;padding:0 4px">
        Livreur à surveiller
    </div>

    @if(!$livreurASurveiller)
        <div style="background:#FFFFFF;border-radius:18px;padding:24px 20px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:24px;color:#9CA3AF;font-size:14px">
            Aucun reliquat en attente.
        </div>
    @else
        @php $ls = $livreurASurveiller; @endphp
        <div style="background:#FFFFFF;border-radius:18px;padding:20px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:24px">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:46px;height:46px;border-radius:14px;background:#FEF2F2;display:flex;align-items:center;justify-content:center;font-size:22px">
                        ⚠️
                    </div>
                    <div>
                        <div style="font-size:16px;font-weight:800;color:#111827">
                            {{ $ls['livreur']->prenom }} {{ $ls['livreur']->nom }}
                        </div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px">Reliquat le plus élevé</div>
                    </div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:16px;font-weight:800;color:#EF4444">
                        {{ number_format($ls['reliquat_total'], 0, ',', ' ') }}
                    </div>
                    <div style="font-size:11px;color:#9CA3AF">FCFA</div>
                </div>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- 5. TOP ABONNÉS                                          --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div style="font-size:11px;font-weight:800;color:#6B7280;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;padding:0 4px">
        Top abonnés
    </div>

    @if($topAbonnes->isEmpty() || $topAbonnes->first()->total_pains == 0)
        <div style="background:#FFFFFF;border-radius:18px;padding:24px 20px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:24px;color:#9CA3AF;font-size:14px">
            Aucune consommation enregistrée.
        </div>
    @else
        <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:24px">
            @foreach($topAbonnes as $i => $abonne)
                @if(($abonne->total_pains ?? 0) > 0)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;{{ !$loop->last ? 'border-bottom:1px solid #E5E7EB' : '' }}">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:28px;height:28px;border-radius:8px;background:#F3F4F6;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#6B7280;flex-shrink:0">
                                {{ $i + 1 }}
                            </div>
                            <span style="font-size:14px;font-weight:600;color:#111827">
                                {{ $abonne->prenom }} {{ $abonne->nom }}
                            </span>
                        </div>
                        <span style="font-size:14px;font-weight:700;color:#3B82F6">
                            {{ number_format($abonne->total_pains, 0, ',', ' ') }}
                            <span style="font-size:11px;font-weight:500;color:#9CA3AF">pains</span>
                        </span>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- 6. ÉVOLUTION MENSUELLE                                  --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div style="font-size:11px;font-weight:800;color:#6B7280;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;padding:0 4px">
        Évolution mensuelle
    </div>

    @if($evolutionsMensuelles->isEmpty())
        <div style="background:#FFFFFF;border-radius:18px;padding:32px 20px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:24px;color:#9CA3AF;font-size:14px">
            <div style="font-size:32px;margin-bottom:10px">📊</div>
            Aucune donnée de production.
        </div>
    @else
        @php
            $maxPains = $evolutionsMensuelles->max('total_pains');
        @endphp
        <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:24px">
            @foreach($evolutionsMensuelles as $i => $ev)
                @php
                    $nomMois   = ucfirst(\Carbon\Carbon::create()->month($ev->mois)->locale('fr')->monthName);
                    $largeur   = $maxPains > 0 ? round(($ev->total_pains / $maxPains) * 100) : 0;
                    $estDernier= $loop->last;
                @endphp
                <div style="padding:12px 16px;{{ !$estDernier ? 'border-bottom:1px solid #E5E7EB' : '' }}">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                        <span style="font-size:13px;font-weight:600;color:#374151">{{ $nomMois }} {{ $ev->annee }}</span>
                        <span style="font-size:13px;font-weight:800;color:#F97316">
                            {{ number_format($ev->total_pains, 0, ',', ' ') }}
                            <span style="font-size:11px;font-weight:500;color:#9CA3AF">pains</span>
                        </span>
                    </div>
                    <div style="height:6px;background:#F3F4F6;border-radius:3px;overflow:hidden">
                        <div style="height:100%;width:{{ $largeur }}%;background:#F97316;border-radius:3px;transition:width .3s"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════ --}}
    {{-- 7. ALERTES                                              --}}
    {{-- ════════════════════════════════════════════════════════ --}}
    <div style="font-size:11px;font-weight:800;color:#D97706;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;padding:0 4px">
        Alertes
    </div>

    @php
        $alertes = [];
        if ($distributionsEnAttente > 0) {
            $alertes[] = [
                'icone'   => '📬',
                'message' => $distributionsEnAttente . ' distribution' . ($distributionsEnAttente > 1 ? 's' : '') . ' en attente',
                'couleur' => '#D97706',
                'bg'      => '#FFFBEB',
                'border'  => '#FCD34D',
            ];
        }
        if ($totalReliquats > 0) {
            $alertes[] = [
                'icone'   => '💸',
                'message' => 'Reliquats non réglés : ' . number_format($totalReliquats, 0, ',', ' ') . ' FCFA',
                'couleur' => '#D97706',
                'bg'      => '#FFFBEB',
                'border'  => '#FCD34D',
            ];
        }
        if ($farineFaible) {
            $alertes[] = [
                'icone'   => '🌾',
                'message' => 'Stock farine faible : ' . ($farine->stock_actuel ?? 0) . ' sac' . (($farine->stock_actuel ?? 0) > 1 ? 's' : '') . ' restant' . (($farine->stock_actuel ?? 0) > 1 ? 's' : ''),
                'couleur' => '#EF4444',
                'bg'      => '#FEF2F2',
                'border'  => '#FCA5A5',
            ];
        }
        if ($levureFaible) {
            $alertes[] = [
                'icone'   => '🧪',
                'message' => 'Stock levure faible : ' . ($levure->stock_actuel ?? 0) . ' paquet' . (($levure->stock_actuel ?? 0) > 1 ? 's' : '') . ' restant' . (($levure->stock_actuel ?? 0) > 1 ? 's' : ''),
                'couleur' => '#EF4444',
                'bg'      => '#FEF2F2',
                'border'  => '#FCA5A5',
            ];
        }
    @endphp

    @if(empty($alertes))
        <div style="background:#ECFDF5;border:1.5px solid #A7F3D0;border-radius:14px;padding:16px 20px;margin-bottom:16px;display:flex;align-items:center;gap:12px">
            <span style="font-size:20px">✅</span>
            <span style="font-size:14px;font-weight:600;color:#065F46">Tout est en ordre.</span>
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px">
            @foreach($alertes as $alerte)
                <div style="background:{{ $alerte['bg'] }};border:1.5px solid {{ $alerte['border'] }};border-radius:14px;padding:14px 16px;display:flex;align-items:center;gap:12px">
                    <span style="font-size:20px;flex-shrink:0">{{ $alerte['icone'] }}</span>
                    <span style="font-size:14px;font-weight:600;color:{{ $alerte['couleur'] }}">
                        ⚠️ {{ $alerte['message'] }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif

@endsection
