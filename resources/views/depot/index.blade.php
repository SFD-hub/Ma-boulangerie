@extends('layouts.app')

@section('title', 'Boutique')

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('dashboard') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Boutique</span>
        <div style="width:36px"></div>
    </div>

    {{-- Choix de la date --}}
    <div style="margin-bottom:16px">
        <input type="date" value="{{ $date }}" max="{{ date('Y-m-d') }}"
               onchange="window.location = '{{ route('depot.index') }}?date=' + this.value"
               style="padding:8px 12px;border-radius:10px;border:1px solid var(--border);font-size:13px;color:#111827;background:#FFFFFF">
    </div>

    {{-- ── Formulaire d'ajout ── --}}
    <div style="background:#FFFFFF;border-radius:18px;padding:18px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">
        <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:14px">Enregistrer une vente</div>

        @if($errors->any())
            <div class="alert-danger">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('depot.store') }}">
            @csrf
            <input type="hidden" name="date_vente" value="{{ $date }}">

            @if($produits->isEmpty())
                <p style="font-size:13px;color:#EF4444;margin:0 0 12px">
                    Aucun produit configuré. <a href="{{ route('produits.create') }}">Créez-en un</a> avant de continuer.
                </p>
            @elseif($produits->count() > 1)
                <div class="form-group">
                    <label class="form-label">Produit *</label>
                    <select class="form-input" name="produit_id" required>
                        @foreach($produits as $produit)
                            <option value="{{ $produit->id }}" {{ old('produit_id', $defaultProduitId) == $produit->id ? 'selected' : '' }}>
                                {{ $produit->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="produit_id" value="{{ $produits->first()->id }}">
            @endif

            <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:4px">Pains vendus *</div>
            @php
                $lignes = [
                    ['id' => 'Entiers', 'name' => 'nombre_entiers', 'label' => 'Entier', 'prix' => $prix['entier']],
                    ['id' => 'Moities', 'name' => 'nombre_moities', 'label' => 'Moitié', 'prix' => $prix['moitie']],
                    ['id' => 'Tiers',   'name' => 'nombre_tiers',   'label' => 'Tiers',  'prix' => $prix['tiers']],
                ];
            @endphp
            @foreach($lignes as $l)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #F3F4F6">
                    <div>
                        <div style="font-size:15px;font-weight:600;color:#111827">{{ $l['label'] }}</div>
                        <div style="font-size:12px;color:#6B7280">{{ number_format($l['prix'], 0, ',', ' ') }} FCFA</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <button type="button" onclick="changerPiece('piece{{ $l['id'] }}', -1)"
                                style="width:40px;height:40px;border-radius:12px;border:1px solid var(--border);background:#fff;font-size:22px;font-weight:700;color:#111827;cursor:pointer">−</button>
                        <input type="number" name="{{ $l['name'] }}" id="piece{{ $l['id'] }}" min="0" inputmode="numeric"
                               data-prix="{{ $l['prix'] }}" value="{{ old($l['name'], 0) }}" oninput="calcPieces()"
                               style="width:56px;text-align:center;font-size:18px;font-weight:700;border:none;background:transparent;color:#111827">
                        <button type="button" onclick="changerPiece('piece{{ $l['id'] }}', 1)"
                                style="width:40px;height:40px;border-radius:12px;border:none;background:#F97316;font-size:22px;font-weight:700;color:#fff;cursor:pointer">+</button>
                    </div>
                </div>
            @endforeach

            <div style="display:flex;justify-content:space-between;align-items:baseline;margin:16px 0">
                <span style="font-size:14px;font-weight:600;color:#374151">Total</span>
                <span style="font-size:22px;font-weight:800;color:#111827"><span id="totalMontant">0</span> FCFA</span>
            </div>

            <button type="submit" class="btn btn-primary btn-full" {{ $produits->isEmpty() ? 'disabled' : '' }}>
                Enregistrer la vente
            </button>
        </form>
    </div>

    <script>
        const ids = ['pieceEntiers', 'pieceMoities', 'pieceTiers'];

        function valeur(id) {
            return Math.max(0, parseInt(document.getElementById(id)?.value || 0) || 0);
        }

        function changerPiece(id, delta) {
            document.getElementById(id).value = Math.max(0, valeur(id) + delta);
            calcPieces();
        }

        function calcPieces() {
            const total = ids.reduce((somme, id) => somme + valeur(id) * parseInt(document.getElementById(id).dataset.prix), 0);
            document.getElementById('totalMontant').textContent = total.toLocaleString('fr-FR');
        }
        document.addEventListener('DOMContentLoaded', calcPieces);
    </script>

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

    {{-- ── Liste des ventes du jour ── --}}
    @if($ventes->isEmpty())
        <div class="card">
            <div class="empty-state">
                <p style="font-size:32px;margin-bottom:8px">🏪</p>
                <p>Aucune vente enregistrée le {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.</p>
            </div>
        </div>
    @else
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
