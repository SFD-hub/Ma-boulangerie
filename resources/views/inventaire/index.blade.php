@extends('layouts.app')

@section('title', 'Inventaire')

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
        <span style="font-size:17px;font-weight:700;color:#111827">Inventaire</span>
        <div style="flex:1"></div>
    </div>

    {{-- ── Comptage : une ligne par produit, laisser vide = non compté ── --}}
    <form method="POST" action="{{ route('inventaire.store') }}" onsubmit="this.querySelector('button[type=submit]').disabled = true"
          style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:22px">
        @csrf

        @foreach($produits as $p)
            <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid #E5E7EB">
                <div style="width:44px;height:44px;border-radius:12px;background:{{ $p->fond() }};display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">
                    {{ $p->icone() }}
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:14px;font-weight:700;color:#111827">{{ $p->nom }}</div>
                    <div style="font-size:12px;color:#9CA3AF;margin-top:2px">
                        En stock : {{ \App\Support\Nombre::qte($p->stock_actuel) }} {{ $p->uniteLibelle($p->stock_actuel) }}
                    </div>
                </div>
                <input type="number" name="compte[{{ $p->id }}]" min="0" inputmode="decimal"
                       step="{{ $p->retrait_manuel ? '1' : '0.5' }}" placeholder="Compté" value="{{ old('compte.' . $p->id) }}"
                       style="width:100px;text-align:center;font-size:16px;font-weight:700;padding:10px 8px;border:1px solid #E5E7EB;border-radius:12px;background:#F9FAFB;color:#111827">
            </div>
        @endforeach

        <div style="padding:14px 16px">
            <input class="form-input" type="text" name="note" maxlength="255" placeholder="Remarque (facultatif)" value="{{ old('note') }}" style="margin-bottom:12px">
            <button type="submit" class="btn btn-primary btn-full">Enregistrer l'inventaire</button>
        </div>
    </form>

    {{-- ── Historique des inventaires ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
        <span style="font-size:15px;font-weight:700;color:#111827">Écarts enregistrés</span>
        @if($historique->isNotEmpty())
            <a href="{{ route('inventaire.historique') }}"
               style="font-size:13px;font-weight:600;color:#F97316;text-decoration:none">Voir plus</a>
        @endif
    </div>

    @if($historique->isEmpty())
        <div style="text-align:center;padding:32px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:36px;margin-bottom:10px">📋</div>
            Aucun écart enregistré.
        </div>
    @else
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">
            @foreach($historique as $inv)
                @include('inventaire._ligne')
            @endforeach
        </div>

    @endif

@endsection
