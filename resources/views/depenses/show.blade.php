@extends('layouts.app')

@section('title', 'Détail dépense')

@section('content')

    @php
        $libelles = \App\Http\Controllers\DepenseController::CATEGORIES_LIBELLES;
        $libelle  = $libelles[$depense->categorie] ?? $depense->categorie;
        $isAuto   = in_array($depense->categorie, ['achat_farine', 'achat_levure']);
    @endphp

    {{-- ── En-tête ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('depenses.index') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Détail dépense</span>
        <div style="width:36px"></div>
    </div>

    {{-- ── Carte principale ── --}}
    <div class="card" style="text-align:center;padding:28px 20px 22px">
        <div style="display:flex;justify-content:center;margin-bottom:14px">
            <x-depense-icone :categorie="$depense->categorie" :size="64" />
        </div>
        <div style="font-size:22px;font-weight:800;color:#111827;margin-bottom:4px">
            {{ number_format($depense->montant, 0, ',', ' ') }}&nbsp;<span style="font-size:14px;font-weight:600;color:#6B7280">FCFA</span>
        </div>
        <div style="font-size:13px;color:#6B7280">{{ $libelle }}</div>
    </div>

    {{-- ── Détails ── --}}
    <div class="card">
        <div class="detail-row">
            <span class="detail-row-label">Motif</span>
            <span class="detail-row-value">{{ $depense->libelle }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Catégorie</span>
            <span class="detail-row-value">{{ $libelle }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Montant</span>
            <span class="detail-row-value orange">{{ number_format($depense->montant, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="detail-row" style="border-bottom:none">
            <span class="detail-row-label">Date</span>
            <span class="detail-row-value">{{ $depense->date_depense->format('d/m/Y') }}</span>
        </div>
    </div>

    @if($isAuto)
        <div style="background:#FFF7ED;border-radius:12px;padding:12px 16px;font-size:13px;color:#9A3412;margin-bottom:16px">
            Cette dépense a été créée automatiquement lors d'un achat de stock.
            Pour la modifier, allez dans <strong>Stock → Historique achats</strong>.
        </div>
    @else
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <a href="{{ route('depenses.edit', $depense) }}"
               style="display:flex;align-items:center;justify-content:center;gap:8px;padding:14px;background:#F9FAFB;border-radius:12px;font-size:14px;font-weight:600;color:#111827;text-decoration:none;border:1.5px solid #E5E7EB">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>
            <form method="POST" action="{{ route('depenses.destroy', $depense) }}"
                  onsubmit="return confirm('Supprimer cette dépense ?')">
                @csrf @method('DELETE')
                <button type="submit"
                        style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:14px;background:#FEF2F2;border-radius:12px;font-size:14px;font-weight:600;color:#EF4444;border:1.5px solid #FECACA;cursor:pointer">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Supprimer
                </button>
            </form>
        </div>
    @endif

@endsection
