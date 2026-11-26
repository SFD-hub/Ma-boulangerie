@extends('layouts.app')

@section('title', $livreur->prenom . ' ' . $livreur->nom)

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ $livreur->urlRetour() }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Détail {{ mb_strtolower($livreur->nomRole()) }}</span>
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
        $distributionsTriees = $livreur->distributions->sortByDesc(
            fn ($d) => $d->date_distribution->format('Y-m-d') . ' ' . $d->created_at->format('H:i:s')
        );
        $derniereDist      = $distributionsTriees->first();
        $aVersement        = $derniereDist?->versement !== null;
        $painsAujd         = $derniereDist ? $derniereDist->pains_donnes : 0;
        $montantAttendu    = $derniereDist ? (float) $derniereDist->montant_attendu : 0;
        $statutAujd        = $derniereDist?->statut ?? 'en_attente';
        $regleeSurplus     = !$aVersement && ($statutAujd === 'reglee');
    @endphp

    <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">

        @if($derniereDist)
            <div style="padding:14px 16px 0;font-size:12px;color:#9CA3AF;font-weight:500">
                Dernière distribution — {{ $derniereDist->produit->nom ?? '—' }} · {{ $derniereDist->date_distribution->format('d/m/Y') }}
                <span style="color:#F97316;font-weight:600">· {{ $derniereDist->moment_icon }} {{ $derniereDist->moment }} ({{ $derniereDist->heure_enregistrement }})</span>
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

        {{-- Retour : pains non donnés rapportés le jour même (remis au stock) --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #E5E7EB">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:9px;height:9px;border-radius:50%;background:#8B5CF6;flex-shrink:0"></div>
                <span style="font-size:14px;color:#374151">Retour</span>
            </div>
            @if($derniereDist && (int) $derniereDist->retours_parts > 0)
                <span style="font-size:14px;font-weight:700;color:#8B5CF6">{{ $derniereDist->retours_texte }}</span>
            @else
                <span style="font-size:13px;color:#9CA3AF">—</span>
            @endif
        </div>

        {{-- Pains vendus --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #E5E7EB">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:9px;height:9px;border-radius:50%;background:#10B981;flex-shrink:0"></div>
                <span style="font-size:14px;color:#374151">Pains vendus</span>
            </div>
            @if($aVersement)
                <span style="font-size:14px;font-weight:700;color:#10B981">{{ \App\Support\Nombre::pains($derniereDist->pains_vendus) }}</span>
            @elseif($regleeSurplus)
                <span style="font-size:13px;color:#9CA3AF">—</span>
            @else
                <span style="font-size:13px;font-style:italic;color:#9CA3AF">En attente</span>
            @endif
        </div>

        {{-- Restants --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #E5E7EB">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:9px;height:9px;border-radius:50%;background:#F97316;flex-shrink:0"></div>
                <span style="font-size:14px;color:#374151">Restants</span>
            </div>
            @if($aVersement)
                <span style="font-size:14px;font-weight:700;color:#F97316">{{ \App\Support\Nombre::pains($derniereDist->invendus) }}</span>
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
        @php $distributionDuJour = $livreur->distributionDuJour(); @endphp
        <button type="button" onclick="ouvrirSheet('sheetRetour')" {{ $distributionDuJour ? '' : 'disabled' }}
           style="display:flex;align-items:center;justify-content:center;gap:8px;background:transparent;color:#F97316;border:2px solid #F97316;border-radius:14px;padding:15px 20px;font-size:16px;font-weight:700;box-sizing:border-box;cursor:pointer;font-family:inherit;width:100%;{{ $distributionDuJour ? '' : 'opacity:.5;cursor:not-allowed' }}">
            ↩️ Enregistrer un retour
        </button>
        @if($livreur->estVendeur())
            @php
                $enCoursVendeur = $livreur->distributionEnCours();
                $enMainVendeur  = $enCoursVendeur ? $enCoursVendeur->partsEnMain() : 0;
            @endphp
            <a href="{{ route('livreurs.vendre.form', $livreur) }}"
               style="display:flex;align-items:center;justify-content:center;gap:8px;background:transparent;color:#F97316;border:2px solid #F97316;border-radius:14px;padding:15px 20px;font-size:16px;font-weight:700;text-decoration:none;box-sizing:border-box;{{ $enCoursVendeur ? '' : 'opacity:.5;pointer-events:none' }}">
                🥖 Enregistrer une vente
                @if($enCoursVendeur)
                    <span style="font-size:12px;font-weight:600;color:#6B7280">({{ \App\Support\Nombre::pains($enMainVendeur / 6) }} en main)</span>
                @endif
            </a>
        @endif
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

    @php $apercuDist = $distributionsTriees->take(3); @endphp

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
                            {{ $dist->produit->nom ?? '—' }} · {{ $dist->date_distribution->format('d/m/Y') }}
                            <span style="font-size:12px;font-weight:600;color:#9CA3AF">{{ $dist->moment_icon }} {{ $dist->heure_enregistrement }}</span>
                        </span>
                        @php $distEstReglee = (float) $dist->reliquat <= 0; @endphp
                        <span style="font-size:12px;font-weight:600;padding:3px 9px;border-radius:20px;
                            {{ $distEstReglee
                                ? 'background:#ECFDF5;color:#059669'
                                : 'background:#FFFBEB;color:#D97706' }}">
                            {{ $distEstReglee ? 'Réglée' : 'En attente' }}
                        </span>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px">
                        <span style="font-size:13px;color:#6B7280">
                            {{ $dist->pains_confies_texte }}
                            @if($dist->versement)
                                · Versé&nbsp;: {{ number_format($dist->versement->montant_verse, 0, ',', ' ') }}&nbsp;FCFA
                                @if((float) $dist->reliquat > 0)
                                    · <span style="color:#EF4444;font-weight:600">Reliquat&nbsp;: {{ number_format($dist->reliquat, 0, ',', ' ') }}&nbsp;FCFA</span>
                                @endif
                            @endif
                        </span>
                        @if((float) $dist->reliquat > 0)
                            <a href="{{ route('livreurs.verser.form', $livreur) }}?distribution_id={{ $dist->id }}"
                               style="flex-shrink:0;font-size:12px;font-weight:700;color:#FFFFFF;background:#F97316;border-radius:20px;padding:5px 12px;text-decoration:none">
                                Régler
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Fenêtre « Retour de pains » (ouverte depuis le bouton de la fiche) ── --}}
    @php $distributionDuJour = $distributionDuJour ?? $livreur->distributionDuJour(); @endphp
    @if($livreur->actif && $distributionDuJour)
        @php
            $estVendeurRetour = $livreur->estVendeur();
            $maxRetourParts   = $distributionDuJour->retourMaxParts($estVendeurRetour);
            $retoursListe     = $distributionDuJour->retours()->orderByDesc('id')->get();
            // Un livreur/client rapporte des pains entiers ; un vendeur de la Boutique aussi des moitiés et tiers.
            $lignesRetour = $estVendeurRetour
                ? [['id' => 'retE', 'name' => 'nombre_entiers', 'label' => 'Entiers', 'poids' => 6],
                   ['id' => 'retM', 'name' => 'nombre_moities', 'label' => 'Moitiés', 'poids' => 3],
                   ['id' => 'retT', 'name' => 'nombre_tiers',   'label' => 'Tiers',   'poids' => 2]]
                : [['id' => 'retE', 'name' => 'nombre_entiers', 'label' => 'Pains entiers rapportés', 'poids' => 6]];
        @endphp

        <style>
            .sheet-fond { display:none;position:fixed;inset:0;background:rgba(17,24,39,.5);z-index:200;align-items:center;justify-content:center;padding:16px;box-sizing:border-box }
            .sheet { background:#FFFFFF;width:100%;max-width:420px;border-radius:18px;padding:24px 22px 22px;box-sizing:border-box;max-height:90vh;overflow-y:auto;box-shadow:0 20px 50px rgba(0,0,0,.25) }
            .sheet-titre { font-size:18px;font-weight:800;color:#111827;margin-bottom:6px }
        </style>

        <div id="sheetRetour" class="sheet-fond" onclick="if (event.target === this) fermerSheets()">
            <div class="sheet">
                <div class="sheet-titre" style="margin-bottom:16px">{{ $estVendeurRetour ? 'Pains non vendus rapportés' : 'Retour de pains' }}</div>

                <div style="display:flex;align-items:center;justify-content:center;gap:18px;background:#F9FAFB;border-radius:14px;padding:14px;margin-bottom:16px">
                    <div style="text-align:center">
                        <div style="font-size:12px;color:#9CA3AF">{{ $estVendeurRetour ? 'En main' : 'Confiés' }}</div>
                        <div style="font-size:22px;font-weight:800;color:#111827;line-height:1.2">{{ \App\Support\Nombre::pains(($estVendeurRetour ? $maxRetourParts : $distributionDuJour->parts_confiees) / 6) }}</div>
                    </div>
                    <div style="font-size:22px;color:#9CA3AF">→</div>
                    <div style="text-align:center">
                        <div style="font-size:12px;color:#9CA3AF">Après retour</div>
                        <div id="retourApres" style="font-size:22px;font-weight:800;color:#6D28D9;line-height:1.2">—</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('livreurs.retour', $livreur) }}" onsubmit="this.querySelector('button[type=submit]').disabled = true">
                    @csrf
                    @foreach($lignesRetour as $l)
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0 10px">
                            <div style="font-size:15px;font-weight:600;color:#111827">{{ $l['label'] }}</div>
                            <div style="display:flex;align-items:center;gap:8px">
                                <button type="button" onclick="changerRetour('{{ $l['id'] }}', -1)"
                                        style="width:38px;height:38px;border-radius:12px;border:1px solid var(--border);background:#fff;font-size:22px;font-weight:700;color:#111827;cursor:pointer">−</button>
                                <input type="number" name="{{ $l['name'] }}" id="{{ $l['id'] }}" min="0" inputmode="numeric"
                                       data-poids="{{ $l['poids'] }}" value="{{ $loop->first ? 1 : 0 }}" oninput="majRetour()"
                                       style="width:56px;text-align:center;font-size:18px;font-weight:700;border:none;background:transparent;color:#111827">
                                <button type="button" onclick="changerRetour('{{ $l['id'] }}', 1)"
                                        style="width:38px;height:38px;border-radius:12px;border:none;background:#F97316;font-size:22px;font-weight:700;color:#fff;cursor:pointer">+</button>
                            </div>
                        </div>
                    @endforeach
                    <div style="display:flex;gap:10px;margin-top:10px">
                        <button type="button" onclick="fermerSheets()" class="btn btn-secondary" style="flex:1">Fermer</button>
                        <button type="submit" id="retourEnvoyer" class="btn btn-primary" style="flex:2" {{ $maxRetourParts <= 0 ? 'disabled' : '' }}>Enregistrer</button>
                    </div>
                </form>

                @if($retoursListe->isNotEmpty())
                    <div style="font-size:13px;font-weight:700;color:#111827;margin:20px 0 6px">Retours saisis</div>
                    @foreach($retoursListe as $retour)
                        <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-top:1px solid #F3F4F6">
                            <div style="flex:1;min-width:0">
                                <div style="font-size:14px;font-weight:600;color:#111827">{{ $retour->descriptionPieces() }}</div>
                                <div style="font-size:12px;color:#9CA3AF">{{ $retour->created_at->format('d/m/Y à H:i') }}</div>
                            </div>
                            <form method="POST" action="{{ route('retours-distribution.destroy', $retour) }}"
                                  onsubmit="return confirm('Annuler ce retour ?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="font-size:13px;font-weight:600;color:#EF4444;background:none;border:none;cursor:pointer;padding:6px 4px">Annuler</button>
                            </form>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <script>
            const baseRetourParts = {{ $estVendeurRetour ? $maxRetourParts : $distributionDuJour->parts_confiees }};
            const maxRetourParts  = {{ $maxRetourParts }};

            // « 3 pains et 1 moitié » : même présentation que Nombre::pains() côté serveur.
            function fmtRetour(p) {
                let e = Math.floor(p / 6), r = p % 6;
                if (r === 1 && e >= 1) { e -= 1; r = 7; }
                const reste = { 0: '', 1: '1 sixième', 2: '1 tiers', 3: '1 moitié', 4: '2 tiers', 5: '1 moitié et 1 tiers', 7: '1 moitié et 2 tiers' }[r];
                if (e === 0) return reste || '0 pain';
                const base = e + ' pain' + (e > 1 ? 's' : '');
                return reste ? base + ((r === 5 || r === 7) ? ', ' : ' et ') + reste : base;
            }
            function partsRetour() {
                let total = 0;
                document.querySelectorAll('#sheetRetour input[data-poids]').forEach(function (el) {
                    total += (parseInt(el.value || 0) || 0) * parseInt(el.dataset.poids);
                });
                return total;
            }
            function majRetour() {
                const p = partsRetour();
                const apres = document.getElementById('retourApres');
                apres.textContent = fmtRetour(Math.max(0, baseRetourParts - p));
                apres.style.color = p > maxRetourParts ? '#DC2626' : '#6D28D9';
                document.getElementById('retourEnvoyer').disabled = (p <= 0 || p > maxRetourParts);
            }
            function changerRetour(id, delta) {
                const el = document.getElementById(id);
                el.value = Math.max(0, (parseInt(el.value || 0) || 0) + delta);
                majRetour();
            }
            function ouvrirSheet(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                majRetour();
            }
            function fermerSheets() {
                document.querySelectorAll('.sheet-fond').forEach(function (el) { el.style.display = 'none'; });
                document.body.style.overflow = '';
            }
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') fermerSheets(); });
        </script>
    @endif

@endsection
