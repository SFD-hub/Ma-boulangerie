@extends('layouts.app')

@section('title', $matierePremiere->nom)

@section('content')

    @php
        $mp       = $matierePremiere;
        $stock    = (float) $mp->stock_actuel;
        $enAlerte = $stock <= (float) $mp->seuil_alerte && (float) $mp->seuil_alerte > 0;
        $unite    = $mp->unite ?: 'unité';

        $dernier   = $dernierRetrait;
        $peutAgir  = $dernier && $dernier->peutEtreAnnuleePar(auth()->user());
    @endphp

    <style>
        .sheet-fond { display:none;position:fixed;inset:0;background:rgba(17,24,39,.5);z-index:200;align-items:center;justify-content:center;padding:16px;box-sizing:border-box }
        .sheet { background:#FFFFFF;width:100%;max-width:420px;border-radius:18px;padding:24px 22px 22px;box-sizing:border-box;max-height:90vh;overflow-y:auto;box-shadow:0 20px 50px rgba(0,0,0,.25) }
        .sheet-poignee { display:none }
        .sheet-titre { font-size:18px;font-weight:800;color:#111827;margin-bottom:16px }
        .sheet-texte { font-size:13px;color:#6B7280;margin-bottom:16px }
        .action-btn { flex:1;display:flex;align-items:center;justify-content:center;gap:6px;border-radius:14px;padding:13px 8px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;border:1px solid transparent }
        .action-btn[disabled] { opacity:.4;cursor:not-allowed }
    </style>

    {{-- ── En-tête ── --}}
    <div style="display:flex;align-items:center;margin-bottom:22px">
        <div style="flex:1">
            <a href="{{ route('matieres-premieres.index') }}"
               style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
        <span style="font-size:17px;font-weight:700;color:#111827">{{ $mp->nom }}</span>
        <div style="flex:1"></div>
    </div>

    @if($errors->any())
        <div class="alert-danger" style="margin-bottom:16px">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    {{-- ── Validation enregistrée : notification flottante avec « Annuler » (10 s) ── --}}
    @if($annulable = session('retrait_annulable'))
        <div class="flash-stack" role="status" aria-live="polite">
            <div class="flash flash-success" data-delai="10000" style="display:flex;align-items:center;gap:12px">
                <span style="min-width:0">✓ {{ $annulable['message'] }}</span>
                <form method="POST" action="{{ route('retraits-stock.destroy', $annulable['id']) }}" style="flex-shrink:0;margin:0">
                    @csrf @method('DELETE')
                    <button type="submit"
                            style="background:#FFFFFF;color:#065F46;border:1px solid #6EE7B7;border-radius:20px;padding:7px 14px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit">
                        Annuler
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- ── Quantité restante et valeur ── --}}
    <div style="background:#FFFFFF;border-radius:18px;padding:18px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid {{ $enAlerte ? '#FECACA' : '#F3F4F6' }};margin-bottom:14px">
        <div style="display:flex;align-items:center;gap:14px">
            <div style="width:60px;height:60px;border-radius:16px;background:{{ $mp->fond() }};display:flex;align-items:center;justify-content:center;font-size:32px;flex-shrink:0">
                {{ $mp->icone() }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;color:{{ $enAlerte ? '#EF4444' : '#9CA3AF' }};font-weight:{{ $enAlerte ? '700' : '500' }}">
                    {{ $enAlerte ? '⚠ Stock faible' : 'Stock disponible' }}
                </div>
                <div style="display:flex;align-items:baseline;gap:8px;margin-top:2px">
                    <span style="font-size:36px;font-weight:800;line-height:1;color:{{ $enAlerte ? '#EF4444' : '#111827' }}">{{ \App\Support\Nombre::qte($stock) }}</span>
                    <span style="font-size:14px;color:#9CA3AF">{{ $mp->uniteLibelle($stock) }}</span>
                </div>
            </div>
        </div>

        <div style="display:grid;margin-top:16px;padding-top:16px;border-top:1px solid #F3F4F6">
            <div style="background:#FFF7ED;border-radius:12px;padding:12px">
                <div style="font-size:12px;color:#9A3412">Valeur du stock</div>
                <div style="font-size:20px;font-weight:800;color:#111827;margin-top:2px">
                    @if($valeur !== null)
                        {{ number_format($valeur, 0, ',', ' ') }}<span style="font-size:12px;font-weight:600;color:#9CA3AF"> FCFA</span>
                    @else
                        <span style="color:#D1D5DB">—</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Sacs / bidons terminés : Terminé · Supprimer la dernière validation ── --}}
    @if($mp->retrait_manuel)
        <div style="background:#FFFFFF;border-radius:18px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:14px">
            <div style="font-size:12px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">
                Dernier {{ $unite }} terminé
            </div>
            <div style="font-size:15px;font-weight:700;color:{{ $dernier ? '#111827' : '#9CA3AF' }}">
                {{ $dernier ? $dernier->date_retrait->format('d/m/Y') : '—' }}
            </div>

            <div style="display:flex;gap:8px;margin-top:14px">
                <button type="button" class="action-btn" onclick="ouvrirSheet('sheetTerminer')" @disabled($stock < 1)
                        style="background:#F97316;color:#FFFFFF">
                    ✓ Terminé
                </button>
                <button type="button" class="action-btn" onclick="ouvrirSheet('sheetSupprimer')" @disabled(! $peutAgir)
                        style="background:#FFFFFF;color:#EF4444;border-color:#FECACA">
                    🗑 Supprimer
                </button>
            </div>
        </div>
    @endif

    {{-- ── Stock initial (stock présent à l'installation, sans dépense) ── --}}
    @if($stocksInitiaux->isNotEmpty())
        <div style="font-size:16px;font-weight:700;color:#111827;margin-top:12px;margin-bottom:12px">Stock initial</div>
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:12px">
            @foreach($stocksInitiaux as $achat)
                @include('matieres-premieres._ligne-achat')
            @endforeach
        </div>
    @endif

    {{-- ── Achats de ce produit ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:12px;margin-bottom:12px">
        <span style="font-size:16px;font-weight:700;color:#111827">Historique des achats</span>
        <a href="{{ route('achats-matieres-premieres.historique', ['matiere' => $mp->id]) }}"
           style="font-size:13px;font-weight:600;color:#F97316;text-decoration:none">Voir plus</a>
    </div>

    @if($achats->isEmpty())
        <div style="text-align:center;padding:28px 0;color:#9CA3AF;font-size:14px">Aucun achat enregistré.</div>
    @else
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:12px">
            @foreach($achats as $achat)
                @include('matieres-premieres._ligne-achat')
            @endforeach
        </div>
    @endif

    @if($mp->retrait_manuel)

        {{-- ── Fiche : Terminé ── --}}
        <div id="sheetTerminer" class="sheet-fond" onclick="if (event.target === this) fermerSheets()">
            <form method="POST" action="{{ route('retraits-stock.store', $mp) }}" class="sheet" onsubmit="this.querySelector('button[type=submit]').disabled = true">
                @csrf
                <div class="sheet-poignee"></div>
                <div class="sheet-titre">{{ ucfirst($unite) }} de {{ mb_strtolower($mp->nom) }} terminé ?</div>

                <div style="display:flex;align-items:center;justify-content:center;gap:18px;background:#F9FAFB;border-radius:14px;padding:14px;margin-bottom:16px">
                    <div style="text-align:center">
                        <div style="font-size:12px;color:#9CA3AF">Stock actuel</div>
                        <div style="font-size:28px;font-weight:800;color:#111827;line-height:1.2">{{ (int) floor($stock) }}</div>
                    </div>
                    <div style="font-size:22px;color:#9CA3AF">→</div>
                    <div style="text-align:center">
                        <div style="font-size:12px;color:#9CA3AF">Après validation</div>
                        <div style="font-size:28px;font-weight:800;color:#C2410C;line-height:1.2">{{ max(0, (int) floor($stock) - 1) }}</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input class="form-input" type="date" name="date_retrait" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Remarque <span style="font-weight:400;color:#9CA3AF">(facultatif)</span></label>
                    <input class="form-input" type="text" name="note" maxlength="255" placeholder="Ex: fini plus vite, bidon percé…">
                </div>
                <div style="display:flex;gap:10px;margin-top:6px">
                    <button type="button" onclick="fermerSheets()" class="btn btn-secondary" style="flex:1">Annuler</button>
                    <button type="submit" class="btn btn-primary" style="flex:2">Valider</button>
                </div>
            </form>
        </div>

        @if($dernier)
            {{-- ── Fiche : Supprimer la dernière validation ── --}}
            <div id="sheetSupprimer" class="sheet-fond" onclick="if (event.target === this) fermerSheets()">
                <form method="POST" action="{{ route('retraits-stock.destroy', $dernier) }}" class="sheet" onsubmit="this.querySelector('button[type=submit]').disabled = true">
                    @csrf @method('DELETE')
                    <div class="sheet-poignee"></div>
                    <div class="sheet-titre">Supprimer la dernière validation ?</div>

                    <div style="display:flex;align-items:center;justify-content:center;gap:18px;background:#FEF2F2;border-radius:14px;padding:14px;margin-bottom:16px">
                        <div style="text-align:center">
                            <div style="font-size:12px;color:#9CA3AF">Stock actuel</div>
                            <div style="font-size:28px;font-weight:800;color:#111827;line-height:1.2">{{ (int) floor($stock) }}</div>
                        </div>
                        <div style="font-size:22px;color:#9CA3AF">→</div>
                        <div style="text-align:center">
                            <div style="font-size:12px;color:#9CA3AF">Après suppression</div>
                            <div style="font-size:28px;font-weight:800;color:#B91C1C;line-height:1.2">{{ (int) floor($stock) + 1 }}</div>
                        </div>
                    </div>

                    <div style="display:flex;gap:10px">
                        <button type="button" onclick="fermerSheets()" class="btn btn-secondary" style="flex:1">Garder</button>
                        <button type="submit" class="btn" style="flex:2;background:#EF4444;color:#FFFFFF">Supprimer</button>
                    </div>
                </form>
            </div>
        @endif

        <script>
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
    @endif

@endsection
