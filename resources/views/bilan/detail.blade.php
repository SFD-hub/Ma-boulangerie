@extends('layouts.app')

@section('title', 'Bilan financier - ' . $moisNom . ' ' . $annee)

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('bilan.index') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Bilan financier</span>
        <div style="width:36px"></div>
    </div>

    {{-- ── Titre période ── --}}
    <div style="text-align:center;font-size:15px;font-weight:700;color:#F97316;margin-bottom:20px;letter-spacing:.02em">
        {{ $moisNom }} {{ $annee }}
    </div>

    {{-- ── RECETTES ── --}}
    <div style="font-size:11px;font-weight:800;color:#059669;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;padding:0 4px">
        Recettes
    </div>

    <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">

        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #E5E7EB">
            <span style="font-size:14px;color:#6B7280">Versements livreurs</span>
            <span style="font-size:14px;font-weight:700;color:#111827">
                {{ number_format($versementsLivreurs, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #E5E7EB">
            <span style="font-size:14px;color:#6B7280">Factures abonnés payées</span>
            <span style="font-size:14px;font-weight:700;color:#111827">
                {{ number_format($facturesAbonnes, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:#F0FDF4">
            <span style="font-size:14px;font-weight:700;color:#065F46">Total recettes</span>
            <span style="font-size:15px;font-weight:800;color:#059669">
                {{ number_format($totalRecettes, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>

    </div>

    {{-- ── DÉPENSES ── --}}
    <div style="font-size:11px;font-weight:800;color:#EF4444;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;padding:0 4px">
        Dépenses
    </div>

    @php
        $lignesDepenses = array_values(array_filter([
            ['label' => 'Farine',                   'icone' => '🌾', 'montant' => $achatFarine],
            ['label' => 'Levure',                   'icone' => '🧪', 'montant' => $achatLevure],
            ['label' => 'Salaires gérant',           'icone' => '👤', 'montant' => $salaireGerant],
            ['label' => 'Salaires employés',          'icone' => '👥', 'montant' => $salaireEmploye],
            ['label' => 'Eau',                      'icone' => '💧', 'montant' => $eau],
            ['label' => 'Électricité',              'icone' => '⚡', 'montant' => $electricite],
            ['label' => 'Carburant',                'icone' => '⛽', 'montant' => $carburant],
            ['label' => 'Transport',                'icone' => '🚗', 'montant' => $transport],
            ['label' => 'Réparation / Maintenance', 'icone' => '🔧', 'montant' => $reparation],
            ['label' => 'Autres',                   'icone' => '📌', 'montant' => $autres],
        ], fn($l) => $l['montant'] > 0));
    @endphp

    <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">

        @if(empty($lignesDepenses))
            <div style="padding:20px 16px;text-align:center;font-size:14px;color:#9CA3AF">
                Aucune dépense ce mois.
            </div>
        @else
            @foreach($lignesDepenses as $ligne)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:13px 16px;border-bottom:1px solid #E5E7EB">
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="font-size:16px">{{ $ligne['icone'] }}</span>
                        <span style="font-size:14px;color:#374151">{{ $ligne['label'] }}</span>
                    </div>
                    <span style="font-size:14px;font-weight:700;color:#111827">
                        {{ number_format($ligne['montant'], 0, ',', ' ') }}&nbsp;FCFA
                    </span>
                </div>
            @endforeach
        @endif

        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:#FEF2F2">
            <span style="font-size:14px;font-weight:700;color:#991B1B">Total dépenses</span>
            <span style="font-size:15px;font-weight:800;color:#EF4444">
                {{ number_format($totalDepenses, 0, ',', ' ') }}&nbsp;FCFA
            </span>
        </div>

    </div>

    {{-- ── RÉSULTAT ── --}}
    <div style="font-size:11px;font-weight:800;color:#6B7280;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;padding:0 4px">
        Résultat du mois
    </div>

    @if($benefice > 0)
        <div style="border-radius:18px;padding:24px 20px;text-align:center;background:#F0FDF4;border:1.5px solid #A7F3D0;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:24px">
            <div style="font-size:13px;font-weight:700;color:#059669;margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em">
                🟢 Bénéfice du mois
            </div>
            <div style="font-size:38px;font-weight:800;color:#065F46;line-height:1">
                +{{ number_format($benefice, 0, ',', ' ') }}&nbsp;<span style="font-size:20px">FCFA</span>
            </div>
        </div>
    @elseif($benefice < 0)
        <div style="border-radius:18px;padding:24px 20px;text-align:center;background:#FEF2F2;border:1.5px solid #FCA5A5;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:24px">
            <div style="font-size:13px;font-weight:700;color:#EF4444;margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em">
                🔴 Perte du mois
            </div>
            <div style="font-size:38px;font-weight:800;color:#991B1B;line-height:1">
                {{ number_format($benefice, 0, ',', ' ') }}&nbsp;<span style="font-size:20px">FCFA</span>
            </div>
        </div>
    @else
        <div style="border-radius:18px;padding:24px 20px;text-align:center;background:#F9FAFB;border:1.5px solid #E5E7EB;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:24px">
            <div style="font-size:13px;font-weight:700;color:#6B7280;margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em">
                ⚪ Équilibre
            </div>
            <div style="font-size:38px;font-weight:800;color:#374151;line-height:1">
                0&nbsp;<span style="font-size:20px">FCFA</span>
            </div>
        </div>
    @endif

    {{-- ── Actions ── --}}
    <div style="display:flex;flex-direction:column;gap:10px">

        {{-- Télécharger PDF --}}
        <a href="{{ route('bilan.imprimer.detail', ['mois' => $mois, 'annee' => $annee]) }}" target="_blank"
           style="display:flex;align-items:center;justify-content:center;gap:8px;padding:15px;border-radius:14px;font-size:15px;font-weight:700;text-decoration:none;border:2px solid #F97316;color:#F97316;background:#FFFFFF">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Télécharger PDF
        </a>

        {{-- Enregistrer le bilan --}}
        <form method="POST" action="{{ route('bilan.enregistrer') }}">
            @csrf
            <input type="hidden" name="mois" value="{{ $mois }}">
            <input type="hidden" name="annee" value="{{ $annee }}">
            <button type="submit"
                    style="display:block;width:100%;padding:15px;border-radius:14px;font-size:15px;font-weight:700;border:none;cursor:pointer;background:#F97316;color:#FFFFFF">
                {{ $dejaEnregistre ? 'Mettre à jour le bilan' : 'Enregistrer le bilan' }}
            </button>
        </form>

    </div>

@endsection
