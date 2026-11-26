@extends('layouts.app')

@section('title', 'Pain restant')

@section('content')

    @php
        $stockPains = intdiv($etat['stock'], 6);
    @endphp

    <style>
        .sheet-fond { display:none;position:fixed;inset:0;background:rgba(17,24,39,.5);z-index:200;align-items:center;justify-content:center;padding:16px;box-sizing:border-box }
        .sheet { background:#FFFFFF;width:100%;max-width:420px;border-radius:18px;padding:24px 22px 22px;box-sizing:border-box;max-height:90vh;overflow-y:auto;box-shadow:0 20px 50px rgba(0,0,0,.25) }
        .sheet-titre { font-size:18px;font-weight:800;color:#111827;margin-bottom:16px }
    </style>

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
        <span style="font-size:17px;font-weight:700;color:#111827">Pain restant</span>
        <div style="flex:1;display:flex;justify-content:flex-end">
            <button type="button" onclick="ouvrirSheet('sheetVente')"
                    style="background:#F97316;color:#FFFFFF;border:none;border-radius:20px;padding:8px 16px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit">
                Vendre
            </button>
        </div>
    </div>

    {{-- ── Stock de pain restant (estimation) ── --}}
    <div style="background:#FFFFFF;border-radius:18px;padding:18px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid {{ $etat['a_ajuster'] ? '#FECACA' : '#F3F4F6' }};margin-bottom:14px">
        <div style="display:flex;align-items:center;gap:14px">
            <div style="width:60px;height:60px;border-radius:16px;background:#FEF3C7;display:flex;align-items:center;justify-content:center;font-size:32px;flex-shrink:0">🥖</div>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;color:#9CA3AF;font-weight:500">Pain restant en stock (estimation)</div>
                <div style="margin-top:2px;line-height:1.1">
                    <span style="font-size:34px;font-weight:800;color:#111827">{{ $stockPains }}</span>
                </div>
                <div style="font-size:13px;color:#9CA3AF;margin-top:2px;font-weight:500">{{ \App\Support\Nombre::painsLegende($etat['stock'] / 6) }}</div>
            </div>
        </div>

        @if($etat['a_ajuster'])
            <div style="margin-top:14px;background:#FEF2F2;border-radius:12px;padding:12px 14px;font-size:13px;color:#B91C1C">
                Stock estimé épuisé : comptez le pain qui reste et ajustez le stock pour repartir sur le bon chiffre.
            </div>
        @endif

        <button type="button" onclick="ouvrirSheet('sheetAjuster')"
                style="margin-top:14px;width:100%;background:{{ $etat['a_ajuster'] ? '#F97316' : 'transparent' }};color:{{ $etat['a_ajuster'] ? '#FFFFFF' : '#F97316' }};border:2px solid #F97316;border-radius:14px;padding:12px 16px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit">
            Ajuster le stock
        </button>
    </div>

    {{-- ── Période ── --}}
    @include('partials.periode', ['action' => route('pain-restant.index')])

    {{-- ── Sacs vendus et argent encaissé sur la période ── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px">
        <div style="background:#D1FAE5;border-radius:18px;padding:18px 14px">
            <div style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:10px;line-height:1.3">Sacs vendus</div>
            <div style="font-size:34px;font-weight:800;color:#111827;line-height:1">{{ number_format($ventes['sacs'], 0, ',', ' ') }}</div>
            <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">sac{{ $ventes['sacs'] > 1 ? 's' : '' }}</div>
        </div>
        <div style="background:#DBEAFE;border-radius:18px;padding:18px 14px">
            <div style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:10px;line-height:1.3">Argent encaissé</div>
            <div style="font-size:34px;font-weight:800;color:#111827;line-height:1">{{ number_format($ventes['montant'], 0, ',', ' ') }}</div>
            <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">FCFA</div>
        </div>
    </div>

    {{-- ── Ventes de sacs ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
        <span style="font-size:15px;font-weight:700;color:#111827">Ventes de sacs</span>
        @if($liste->isNotEmpty())
            <a href="{{ route('pain-restant.historique', ['du' => $du, 'au' => $au]) }}"
               style="font-size:13px;font-weight:600;color:#F97316;text-decoration:none">Voir plus</a>
        @endif
    </div>

    @if($liste->isEmpty())
        <div style="text-align:center;padding:32px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:36px;margin-bottom:10px">🛍️</div>
            Aucune vente sur cette période.
        </div>
    @else
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">
            @foreach($liste as $vente)
                <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:700;color:#111827">{{ $vente->nombre_sacs }} sac{{ $vente->nombre_sacs > 1 ? 's' : '' }}</div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px">
                            {{ $vente->date_vente->format('d/m/Y') }} · ≈ {{ $vente->pains_estimes }} pain{{ $vente->pains_estimes > 1 ? 's' : '' }}
                        </div>
                    </div>
                    <div style="font-size:14px;font-weight:800;color:#111827;flex-shrink:0">{{ number_format($vente->montant, 0, ',', ' ') }}&nbsp;FCFA</div>
                    <form method="POST" action="{{ route('pain-restant.destroy', $vente) }}" onsubmit="return confirm('Supprimer cette vente ?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="font-size:13px;font-weight:600;color:#EF4444;background:none;border:none;cursor:pointer;padding:6px 4px">Supprimer</button>
                    </form>
                </div>
            @endforeach
        </div>

    @endif

    {{-- ── D'où vient le pain restant ── --}}
    <div style="font-size:15px;font-weight:700;color:#111827;margin:8px 0 10px">Détail du stock</div>
    <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">
        @php
            $lignesDetail = [
                ['Restants des livreurs',            $etat['restants_livreurs'], '+'],
                ['Restants des clients',             $etat['restants_clients'],  '+'],
                ['Pain non vendu de la boulangerie', $etat['non_vendu'],         '+'],
                ['Sacs vendus (estimé)',             $etat['sorties_sacs'],      '−'],
                ['Ajustements après comptage',       $etat['ajustements'],       $etat['ajustements'] >= 0 ? '+' : '−'],
            ];
        @endphp
        @foreach($lignesDetail as [$libelle, $parts, $signe])
            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:13px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">
                <span style="font-size:14px;color:#374151">{{ $libelle }}</span>
                <span style="font-size:14px;font-weight:700;color:{{ $signe === '+' ? '#059669' : '#DC2626' }}">
                    {{ $signe }}&nbsp;{{ \App\Support\Nombre::pains(abs($parts) / 6) }}
                </span>
            </div>
        @endforeach
    </div>

    {{-- ══ Fenêtre « Vendre des sacs » ══ --}}
    <div id="sheetVente" class="sheet-fond" onclick="if (event.target === this) fermerSheets()">
        <form method="POST" action="{{ route('pain-restant.store') }}" class="sheet" onsubmit="this.querySelector('button[type=submit]').disabled = true">
            @csrf
            <div class="sheet-titre">Vendre des sacs</div>

            <div class="form-group">
                <label class="form-label">Date</label>
                <input class="form-input" type="date" name="date_vente" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nombre de sacs</label>
                <input class="form-input" type="number" name="nombre_sacs" id="venteSacs" min="1" inputmode="numeric" value="1" required oninput="majEstimation()">
            </div>
            <div class="form-group">
                <label class="form-label">Montant encaissé (FCFA)</label>
                <input class="form-input" type="number" name="montant" min="0" inputmode="numeric" placeholder="Ex: 20 000" required>
            </div>
            <div class="form-group">
                <label class="form-label">Pains estimés dans les sacs <span style="font-weight:400;color:#9CA3AF">(environ {{ $painsParSac }} par sac)</span></label>
                <input class="form-input" type="number" name="pains_estimes" id="ventePains" min="0" inputmode="numeric" value="{{ $painsParSac }}" oninput="this.dataset.modifie = '1'">
            </div>

            <div style="display:flex;gap:10px;margin-top:6px">
                <button type="button" onclick="fermerSheets()" class="btn btn-secondary" style="flex:1">Annuler</button>
                <button type="submit" class="btn btn-primary" style="flex:2">Enregistrer</button>
            </div>
        </form>
    </div>

    {{-- ══ Fenêtre « Ajuster le stock » ══ --}}
    <div id="sheetAjuster" class="sheet-fond" onclick="if (event.target === this) fermerSheets()">
        <form method="POST" action="{{ route('pain-restant.ajuster') }}" class="sheet" onsubmit="this.querySelector('button[type=submit]').disabled = true">
            @csrf
            <div class="sheet-titre">Ajuster le stock</div>

            <div style="display:flex;align-items:center;justify-content:center;gap:18px;background:#F9FAFB;border-radius:14px;padding:14px;margin-bottom:16px">
                <div style="text-align:center">
                    <div style="font-size:12px;color:#9CA3AF">Estimé</div>
                    <div style="font-size:28px;font-weight:800;color:#111827;line-height:1.2">{{ $stockPains }}</div>
                </div>
                <div style="font-size:22px;color:#9CA3AF">→</div>
                <div style="text-align:center">
                    <div style="font-size:12px;color:#9CA3AF">Compté</div>
                    <div id="ajusteApres" style="font-size:28px;font-weight:800;color:#C2410C;line-height:1.2">{{ $stockPains }}</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Pains restants comptés</label>
                <input class="form-input" type="number" name="nombre_pains" id="ajusteNombre" min="0" inputmode="numeric" value="{{ $stockPains }}" required
                       oninput="document.getElementById('ajusteApres').textContent = this.value || 0">
            </div>

            <div style="display:flex;gap:10px;margin-top:6px">
                <button type="button" onclick="fermerSheets()" class="btn btn-secondary" style="flex:1">Annuler</button>
                <button type="submit" class="btn btn-primary" style="flex:2">Enregistrer</button>
            </div>
        </form>
    </div>

    <script>
        const painsParSac = {{ $painsParSac }};

        // L'estimation suit le nombre de sacs tant qu'on ne l'a pas modifiée à la main.
        function majEstimation() {
            const pains = document.getElementById('ventePains');
            if (pains.dataset.modifie === '1') return;
            const sacs = parseInt(document.getElementById('venteSacs').value || 0) || 0;
            pains.value = sacs * painsParSac;
        }
        function ouvrirSheet(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function fermerSheets() {
            document.querySelectorAll('.sheet-fond').forEach(function (el) { el.style.display = 'none'; });
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') fermerSheets(); });
    </script>

@endsection
