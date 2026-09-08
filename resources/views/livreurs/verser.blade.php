@extends('layouts.app')

@section('title', 'Enregistrer versement')

@section('content')

    {{-- ── En-tête ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('livreurs.show', $livreur) }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Enregistrer versement</span>
        <div style="width:36px"></div>
    </div>

    {{-- ── Reliquat total en attente ── --}}
    @if($reliquatTotalEnAttente > 0)
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:14px;padding:14px 16px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:11px;font-weight:700;color:#EF4444;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px">
                    Reliquat total en attente
                </div>
                <div style="font-size:12px;color:#9CA3AF">Somme de tous les reliquats non réglés</div>
            </div>
            <span style="font-size:18px;font-weight:800;color:#EF4444">
                {{ number_format($reliquatTotalEnAttente, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>
    @endif

    @if($distributionsNonReglees->isEmpty())

        <div style="text-align:center;padding:60px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">💰</div>
            Aucune distribution en attente de versement.
            <div style="margin-top:16px">
                <a href="{{ route('livreurs.show', $livreur) }}"
                   style="color:#F97316;font-weight:600;text-decoration:none">← Retour au livreur</a>
            </div>
        </div>

    @else

        {{-- ── Erreurs de validation ── --}}
        @if($errors->any())
            <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:12px;padding:14px 16px;margin-bottom:16px">
                @foreach($errors->all() as $msg)
                    <div style="font-size:13px;color:#EF4444;padding:2px 0">{{ $msg }}</div>
                @endforeach
            </div>
        @endif

        @if(session('error'))
            <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:12px;padding:14px 16px;margin-bottom:16px;font-size:13px;color:#EF4444">
                {{ session('error') }}
            </div>
        @endif

        <div style="background:#FFFFFF;border-radius:18px;padding:20px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07)">

            <form method="POST" action="{{ route('livreurs.verser', $livreur) }}">
                @csrf

                {{-- Livreur (affichage) --}}
                <div style="margin-bottom:18px">
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Livreur</label>
                    <div style="background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:13px 16px;font-size:15px;font-weight:600;color:#111827;display:flex;align-items:center;gap:10px">
                        <div style="width:32px;height:32px;border-radius:8px;background:#F97316;color:#FFFFFF;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0">
                            {{ strtoupper(substr($livreur->prenom, 0, 1)) }}
                        </div>
                        {{ $livreur->prenom }} {{ $livreur->nom }}
                    </div>
                </div>

                {{-- Distribution concernée — dropdown custom --}}
                <div style="margin-bottom:18px;position:relative" id="distDropdownWrapper">
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">
                        Distribution concernée (veille) *
                    </label>

                    {{-- Trigger --}}
                    <div id="distTrigger" onclick="toggleDropdown(event)"
                         style="display:flex;align-items:center;justify-content:space-between;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;cursor:pointer;user-select:none;box-sizing:border-box">
                        <span id="distTriggerLabel" style="font-size:15px;color:#9CA3AF">Sélectionner une distribution</span>
                        <svg id="distChevron" width="18" height="18" fill="none" stroke="#9CA3AF" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;transition:transform .2s">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    {{-- Liste déroulante --}}
                    <div id="distList" style="display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;z-index:50;background:#FFFFFF;border:1px solid #E5E7EB;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.1);overflow:hidden">
                        @foreach($distributionsNonReglees as $dist)
                            @php
                                $aDejaVersement = $dist->versement !== null;
                                $reliquatActuel = (float) $dist->reliquat;
                                $invendusExist  = $dist->nombre_invendus ?? '';
                            @endphp
                            <div onclick="selectDist({{ $dist->id }})"
                                 id="distOption_{{ $dist->id }}"
                                 data-dist-option="{{ $dist->id }}"
                                 data-pains="{{ $dist->pains_attribues }}"
                                 data-prix="{{ $dist->prix_pain ?? $prixPain }}"
                                 data-has-versement="{{ $aDejaVersement ? 1 : 0 }}"
                                 data-reliquat-actuel="{{ $reliquatActuel }}"
                                 data-invendus-existants="{{ $invendusExist }}"
                                 data-label="{{ $dist->produit->nom ?? '—' }} — {{ $dist->date_distribution->format('d/m/Y') }} — {{ $dist->pains_attribues }} pains"
                                 style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;cursor:pointer;{{ !$loop->last ? 'border-bottom:1px solid #E5E7EB;' : '' }}"
                                 onmouseover="this.style.background='#FFF7ED'" onmouseout="this.style.background='#FFFFFF'">
                                <div style="flex:1;min-width:0">
                                    <div style="font-size:14px;font-weight:700;color:#111827">{{ $dist->produit->nom ?? '—' }} · {{ $dist->date_distribution->format('d/m/Y') }}</div>
                                    <div style="font-size:12px;color:#6B7280;margin-top:2px">
                                        {{ $dist->pains_attribues }}&nbsp;pains
                                        @if($aDejaVersement)
                                            &nbsp;·&nbsp;Reliquat&nbsp;:&nbsp;<strong style="color:#EF4444">{{ number_format($reliquatActuel, 0, ',', ' ') }}&nbsp;FCFA</strong>
                                        @endif
                                    </div>
                                </div>
                                <span style="font-size:11px;font-weight:600;padding:3px 9px;border-radius:20px;background:#FFFBEB;color:#D97706;flex-shrink:0;margin-left:8px">En attente</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Input caché --}}
                    <input type="hidden" name="distribution_id" id="distHiddenInput" value="{{ old('distribution_id') }}">
                </div>

                {{-- Résumé dynamique de la distribution --}}
                <div id="distResume" style="display:none;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:14px 16px;margin-bottom:18px">
                    <div style="font-size:11px;font-weight:700;color:#6B7280;margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px">Détail de la distribution</div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0">
                        <span style="color:#6B7280">Pains attribués</span>
                        <span id="rPains" style="font-weight:700;color:#111827">—</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0">
                        <span style="color:#6B7280">Invendus retournés</span>
                        <span id="rInvendus" style="font-weight:700;color:#111827">0</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0">
                        <span style="color:#6B7280">Pains vendus</span>
                        <span id="rVendus" style="font-weight:700;color:#10B981">—</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:14px;padding:8px 0 4px;border-top:1px solid #BBF7D0;margin-top:6px">
                        <span style="font-weight:700;color:#111827">Montant attendu</span>
                        <span id="rAttendu" style="font-weight:700;color:#10B981">—</span>
                    </div>
                    <div id="reliquatLine" style="display:none;justify-content:space-between;font-size:13px;padding:4px 0">
                        <span style="font-weight:600;color:#EF4444">Reliquat du jour</span>
                        <span id="rReliquat" style="font-weight:700;color:#EF4444">—</span>
                    </div>
                    <div id="surplusLine" style="display:none;justify-content:space-between;font-size:13px;padding:4px 0">
                        <span style="font-weight:600;color:#059669">Surplus → anciens reliquats</span>
                        <span id="rSurplus" style="font-weight:700;color:#059669">—</span>
                    </div>
                </div>

                {{-- Invendus retournés --}}
                <div style="margin-bottom:18px">
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Invendus retournés *</label>
                    <input type="number" name="nombre_invendus" min="0"
                           value="{{ old('nombre_invendus', 0) }}" id="invendusInput"
                           placeholder="0" oninput="calcVers()" required
                           style="width:100%;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;font-size:15px;color:#111827;box-sizing:border-box;outline:none">
                </div>

                {{-- Montant versé --}}
                <div style="margin-bottom:24px">
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Montant versé aujourd'hui (FCFA) *</label>
                    <input type="number" name="montant_verse" min="0"
                           value="{{ old('montant_verse') }}" id="montantInput"
                           placeholder="Ex: 35 000" oninput="calcVers()" required
                           style="width:100%;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;font-size:15px;color:#111827;box-sizing:border-box;outline:none">
                    <div id="reliquatHint" style="display:none;font-size:13px;margin-top:8px;font-weight:600;padding:0 4px"></div>
                </div>

                <button type="submit"
                        style="width:100%;background:#F97316;color:#FFFFFF;border:none;border-radius:14px;padding:16px 20px;font-size:16px;font-weight:700;cursor:pointer">
                    Enregistrer le versement
                </button>

            </form>
        </div>

    @endif

    <script>
        let _ddOpen = false;

        function toggleDropdown(e) {
            if (e) e.stopPropagation();
            _ddOpen = !_ddOpen;
            document.getElementById('distList').style.display     = _ddOpen ? 'block' : 'none';
            document.getElementById('distChevron').style.transform = _ddOpen ? 'rotate(180deg)' : '';
        }

        function closeDropdown() {
            _ddOpen = false;
            document.getElementById('distList').style.display     = 'none';
            document.getElementById('distChevron').style.transform = '';
        }

        document.addEventListener('click', function (e) {
            const w = document.getElementById('distDropdownWrapper');
            if (w && !w.contains(e.target)) closeDropdown();
        });

        function selectDist(distId) {
            const option = document.getElementById('distOption_' + distId);
            if (!option) return;

            // Mettre à jour l'input caché
            document.getElementById('distHiddenInput').value = distId;

            // Mettre à jour le trigger
            const triggerLabel = document.getElementById('distTriggerLabel');
            triggerLabel.textContent = option.getAttribute('data-label');
            triggerLabel.style.color = '#111827';
            const trigger = document.getElementById('distTrigger');
            trigger.style.borderColor = '#F97316';
            trigger.style.background  = '#FFF7ED';

            closeDropdown();

            // Gérer le champ invendus selon mode 1 / mode 2
            const hasVersement  = option.getAttribute('data-has-versement') === '1';
            const invendusInput = document.getElementById('invendusInput');
            if (invendusInput) {
                if (hasVersement) {
                    const existants = option.getAttribute('data-invendus-existants');
                    invendusInput.value    = existants !== '' ? existants : '0';
                    invendusInput.readOnly = true;
                    invendusInput.style.background = '#F3F4F6';
                    invendusInput.style.color      = '#9CA3AF';
                } else {
                    invendusInput.readOnly = false;
                    invendusInput.style.background = '#F9FAFB';
                    invendusInput.style.color      = '#111827';
                }
            }

            calcVers();
        }

        function calcVers() {
            const distId = document.getElementById('distHiddenInput')?.value;
            const option = distId ? document.getElementById('distOption_' + distId) : null;
            const res    = document.getElementById('distResume');

            if (!option) { if (res) res.style.display = 'none'; return; }

            const pains          = parseInt(option.getAttribute('data-pains') || 0);
            const prix           = parseInt(option.getAttribute('data-prix')  || 0);
            const hasVersement   = option.getAttribute('data-has-versement') === '1';
            const reliquatActuel = parseFloat(option.getAttribute('data-reliquat-actuel') || 0);
            const invendus       = parseInt(document.getElementById('invendusInput')?.value || 0);
            const verse          = parseFloat(document.getElementById('montantInput')?.value || 0);

            const hint         = document.getElementById('reliquatHint');
            const reliquatLine = document.getElementById('reliquatLine');
            const surplusLine  = document.getElementById('surplusLine');
            const rAttenduEl   = document.getElementById('rAttendu');
            const vendus       = Math.max(0, pains - invendus);

            let reliquat, surplus;

            if (hasVersement) {
                reliquat = Math.max(0, reliquatActuel - verse);
                surplus  = Math.max(0, verse - reliquatActuel);

                document.getElementById('rPains').textContent    = pains + ' pains';
                document.getElementById('rInvendus').textContent = invendus + ' pains';
                document.getElementById('rVendus').textContent   = vendus + ' pains';
                if (rAttenduEl) {
                    rAttenduEl.parentElement.children[0].textContent = 'Reliquat restant dû';
                    rAttenduEl.textContent = reliquatActuel.toLocaleString('fr-FR') + ' FCFA';
                    rAttenduEl.style.color = '#EF4444';
                }
            } else {
                const attendu = vendus * prix;
                reliquat = Math.max(0, attendu - verse);
                surplus  = Math.max(0, verse - attendu);

                document.getElementById('rPains').textContent    = pains + ' pains';
                document.getElementById('rInvendus').textContent = invendus + ' pains';
                document.getElementById('rVendus').textContent   = vendus + ' pains';
                if (rAttenduEl) {
                    rAttenduEl.parentElement.children[0].textContent = 'Montant attendu';
                    rAttenduEl.textContent = attendu.toLocaleString('fr-FR') + ' FCFA';
                    rAttenduEl.style.color = '#10B981';
                }
            }

            res.style.display = 'block';

            if (verse > 0) {
                if (surplus > 0) {
                    hint.textContent   = '✅ Compte soldé — surplus de ' + Math.round(surplus).toLocaleString('fr-FR') + ' FCFA appliqué aux anciens reliquats';
                    hint.style.color   = '#059669';
                    hint.style.display = 'block';
                    reliquatLine.style.display = 'none';
                    document.getElementById('rSurplus').textContent = Math.round(surplus).toLocaleString('fr-FR') + ' FCFA';
                    surplusLine.style.display  = 'flex';
                } else if (reliquat > 0) {
                    hint.textContent   = '⚠️ Reliquat restant : ' + Math.round(reliquat).toLocaleString('fr-FR') + ' FCFA';
                    hint.style.color   = '#EF4444';
                    hint.style.display = 'block';
                    document.getElementById('rReliquat').textContent = Math.round(reliquat).toLocaleString('fr-FR') + ' FCFA';
                    reliquatLine.style.display = 'flex';
                    surplusLine.style.display  = 'none';
                } else {
                    hint.textContent   = '✅ Compte soldé';
                    hint.style.color   = '#10B981';
                    hint.style.display = 'block';
                    reliquatLine.style.display = 'none';
                    surplusLine.style.display  = 'none';
                }
            } else {
                hint.style.display = 'none';
                reliquatLine.style.display = 'none';
                surplusLine.style.display  = 'none';
            }
        }

        // Restaurer la sélection après retour d'erreur de validation
        @if(old('distribution_id'))
            document.addEventListener('DOMContentLoaded', function () {
                selectDist({{ old('distribution_id') }});
            });
        @endif
    </script>

@endsection
