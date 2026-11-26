@extends('layouts.app')

@section('title', 'Stock initial')

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;margin-bottom:22px">
        <div style="flex:1">
            <a href="{{ route('matieres-premieres.index') }}"
               style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
        <span style="font-size:17px;font-weight:700;color:#111827">Stock initial</span>
        <div style="flex:1"></div>
    </div>

    @if($produits->isEmpty())

        <div style="text-align:center;padding:48px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">✅</div>
            Tous les produits ont déjà du stock.
            <div style="margin-top:16px">
                <a href="{{ route('matieres-premieres.index') }}"
                   style="font-size:14px;font-weight:600;color:#F97316;text-decoration:none">← Retour au stock</a>
            </div>
        </div>

    @else

        {{-- Une carte par produit ; une ligne laissée vide n'est pas modifiée --}}
        <form method="POST" action="{{ route('stock-initial.store') }}" onsubmit="this.querySelector('button[type=submit]').disabled = true">
            @csrf

            @foreach($produits as $p)
                <div style="background:#FFFFFF;border-radius:16px;padding:14px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:12px">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px">
                        <div style="width:44px;height:44px;border-radius:12px;background:{{ $p->fond() }};display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">
                            {{ $p->icone() }}
                        </div>
                        <div style="font-size:15px;font-weight:700;color:#111827">{{ $p->nom }}</div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <div>
                            <label style="display:block;font-size:12px;font-weight:600;color:#6B7280;margin-bottom:4px">Quantité ({{ $p->uniteLibelle(2) }})</label>
                            <input class="form-input" type="number" name="quantite[{{ $p->id }}]" min="0" inputmode="decimal"
                                   step="{{ $p->retrait_manuel ? '1' : '0.5' }}" placeholder="0" value="{{ old('quantite.' . $p->id) }}">
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:600;color:#6B7280;margin-bottom:4px">Valeur (FCFA)</label>
                            <input class="form-input" type="number" name="valeur[{{ $p->id }}]" min="0" inputmode="numeric"
                                   placeholder="0" value="{{ old('valeur.' . $p->id) }}">
                        </div>
                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary btn-full" style="margin-top:6px">Enregistrer</button>
        </form>

    @endif

@endsection
