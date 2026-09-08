@extends('layouts.app')

@section('title', 'Stock')

@section('content')

    @php
        $farine = $matieresPremières->firstWhere('nom', 'Farine');
        $levure = $matieresPremières->firstWhere('nom', 'Levure');

        // 4 derniers achats pour l'aperçu (la page historique affiche tout)
        $boulangerie_id = auth()->user()->boulangerie_id;
        $achats = \App\Models\AchatMatierePremiere::whereHas('matierePremiere', fn($q) => $q->where('boulangerie_id', $boulangerie_id))
            ->with('matierePremiere')
            ->orderByDesc('date_achat')
            ->orderByDesc('id')
            ->limit(4)
            ->get();
    @endphp

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('dashboard') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Stock</span>
        <a href="{{ route('achats-matieres-premieres.create') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none;font-size:24px;font-weight:300;line-height:1">
            +
        </a>
    </div>

    {{-- ── Alerte configuration manquante ── --}}
    @if(!$farine || !$levure)
        <div class="alert-strip" style="margin-bottom:16px">
            ⚠️ Matières premières non configurées. Créez Farine et Levure dans les paramètres.
        </div>
    @endif

    {{-- ── Cartes stock ── --}}
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px">

        {{-- Farine --}}
        <div style="background:#FFFFFF;border-radius:16px;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #F3F4F6">
            <div style="width:54px;height:54px;border-radius:14px;background:#FFF3E0;display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0">
                🌾
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:15px;font-weight:700;color:#111827">Farine</div>
                <div style="font-size:13px;color:#9CA3AF;margin-top:2px">Stock disponible</div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                @if($farine)
                    <div style="font-size:30px;font-weight:800;line-height:1;color:{{ $farine->stock_actuel <= $farine->seuil_alerte && $farine->seuil_alerte > 0 ? '#EF4444' : '#111827' }}">
                        {{ \App\Support\Nombre::qte($farine->stock_actuel) }}
                    </div>
                    <div style="font-size:12px;color:#9CA3AF;margin-top:4px">sacs</div>
                @else
                    <div style="font-size:22px;font-weight:700;color:#D1D5DB">—</div>
                @endif
            </div>
        </div>

        {{-- Levure --}}
        <div style="background:#FFFFFF;border-radius:16px;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #F3F4F6">
            <div style="width:54px;height:54px;border-radius:14px;background:#FFF0F0;display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0">
                🧪
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:15px;font-weight:700;color:#111827">Levure</div>
                <div style="font-size:13px;color:#9CA3AF;margin-top:2px">Stock disponible</div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                @if($levure)
                    <div style="font-size:30px;font-weight:800;line-height:1;color:{{ $levure->stock_actuel <= $levure->seuil_alerte && $levure->seuil_alerte > 0 ? '#EF4444' : '#111827' }}">
                        {{ \App\Support\Nombre::qte($levure->stock_actuel) }}
                    </div>
                    <div style="font-size:12px;color:#9CA3AF;margin-top:4px">paquets</div>
                @else
                    <div style="font-size:22px;font-weight:700;color:#D1D5DB">—</div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── Bouton Ajouter achat ── --}}
    <a href="{{ route('achats-matieres-premieres.create') }}"
       style="display:flex;align-items:center;justify-content:center;gap:8px;background:#F97316;color:#FFFFFF;border-radius:14px;padding:16px 20px;font-size:16px;font-weight:700;text-decoration:none;margin-bottom:26px;width:100%;box-sizing:border-box">
        + Ajouter achat
    </a>

    {{-- ── Historique des achats ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
        <span style="font-size:16px;font-weight:700;color:#111827">Historique des achats</span>
        <a href="{{ route('achats-matieres-premieres.index') }}"
           style="font-size:13px;font-weight:600;color:#F97316;text-decoration:none">
            Voir plus
        </a>
    </div>

    @if($achats->isEmpty())
        <div style="text-align:center;padding:36px 0;color:#9CA3AF;font-size:14px">
            Aucun achat enregistré.
        </div>
    @else
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07)">
            @foreach($achats as $achat)
                <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">

                    <div style="width:44px;height:44px;border-radius:12px;background:{{ $achat->matierePremiere->nom === 'Farine' ? '#FFF3E0' : '#FFF0F0' }};display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">
                        {{ $achat->matierePremiere->nom === 'Farine' ? '🌾' : '🧪' }}
                    </div>

                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:700;color:#111827">
                            {{ $achat->matierePremiere->nom }}
                        </div>
                        <div style="font-size:13px;color:#6B7280;margin-top:2px">
                            {{ \App\Support\Nombre::qte($achat->quantite) }}&nbsp;{{ $achat->matierePremiere->nom === 'Farine' ? 'sacs' : 'paquets' }}
                            &nbsp;–&nbsp;{{ number_format($achat->montant, 0, ',', ' ') }}&nbsp;FCFA
                        </div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:1px">
                            {{ \Carbon\Carbon::parse($achat->date_achat)->format('d/m/Y') }}
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    @endif

@endsection
