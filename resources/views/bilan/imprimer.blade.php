<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilan financier - {{ $moisNom }} {{ $annee }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #FFFFFF;
            color: #111827;
            padding: 40px 32px;
            max-width: 600px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 2px solid #F97316;
        }
        .boulangerie-name { font-size: 22px; font-weight: 800; color: #F97316; margin-bottom: 4px; }
        .doc-title { font-size: 13px; color: #6B7280; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 2px; }
        .periode { font-size: 16px; font-weight: 700; color: #111827; }

        .section-label {
            font-size: 11px; font-weight: 800; text-transform: uppercase;
            letter-spacing: .08em; margin: 20px 0 8px; padding-left: 2px;
        }
        .section-label.recettes  { color: #059669; }
        .section-label.depenses  { color: #EF4444; }
        .section-label.resultat  { color: #6B7280; }

        .card { border: 1px solid #F3F4F6; border-radius: 12px; overflow: hidden; margin-bottom: 4px; }
        .row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 14px; border-bottom: 1px solid #F9FAFB; font-size: 13px;
        }
        .row:last-child { border-bottom: none; }
        .row .label { color: #6B7280; }
        .row .amount { font-weight: 700; color: #111827; }
        .row.total-rec  { background: #F0FDF4; }
        .row.total-rec .label  { font-weight: 700; color: #065F46; }
        .row.total-rec .amount { font-weight: 800; color: #059669; font-size: 14px; }
        .row.total-dep  { background: #FEF2F2; }
        .row.total-dep .label  { font-weight: 700; color: #991B1B; }
        .row.total-dep .amount { font-weight: 800; color: #EF4444; font-size: 14px; }

        .row-icon { display: flex; align-items: center; gap: 8px; }
        .icone { font-size: 14px; }

        .result-box {
            border-radius: 12px; padding: 20px; text-align: center; margin-top: 8px;
        }
        .result-box.benefice { background: #F0FDF4; border: 1.5px solid #A7F3D0; }
        .result-box.perte    { background: #FEF2F2; border: 1.5px solid #FCA5A5; }
        .result-box.equilibre{ background: #F9FAFB; border: 1.5px solid #E5E7EB; }
        .result-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px; }
        .result-amount { font-size: 30px; font-weight: 800; line-height: 1; }
        .benefice .result-title  { color: #059669; }
        .benefice .result-amount { color: #065F46; }
        .perte .result-title     { color: #EF4444; }
        .perte .result-amount    { color: #991B1B; }
        .equilibre .result-title { color: #6B7280; }
        .equilibre .result-amount{ color: #374151; }

        .footer {
            text-align: center; font-size: 11px; color: #9CA3AF;
            margin-top: 32px; padding-top: 16px; border-top: 1px solid #F3F4F6;
        }

        .print-btn {
            display: block; margin: 0 auto 24px;
            background: #F97316; color: #FFFFFF; border: none;
            border-radius: 12px; padding: 12px 28px; font-size: 15px;
            font-weight: 700; cursor: pointer;
        }
        @media print {
            .print-btn { display: none !important; }
            body { padding: 20px; }
        }
    </style>
</head>
<body onload="window.print()">

    <button class="print-btn" onclick="window.print()">🖨️ Imprimer / Enregistrer PDF</button>

    {{-- En-tête --}}
    <div class="header">
        <div class="boulangerie-name">{{ $boulangerieName }}</div>
        <div class="doc-title">Bilan financier</div>
        <div class="periode">{{ $moisNom }} {{ $annee }}</div>
    </div>

    {{-- Recettes --}}
    <div class="section-label recettes">Recettes</div>
    <div class="card">
        <div class="row">
            <span class="label">Versements livreurs</span>
            <span class="amount">{{ number_format($versementsLivreurs, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="row">
            <span class="label">Factures abonnés payées</span>
            <span class="amount">{{ number_format($facturesAbonnes, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="row">
            <span class="label">Ventes Dépôt</span>
            <span class="amount">{{ number_format($ventesDepot, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="row total-rec">
            <span class="label">Total recettes</span>
            <span class="amount">{{ number_format($totalRecettes, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>

    {{-- Dépenses --}}
    @php
        $lignes = array_values(array_filter([
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

    <div class="section-label depenses">Dépenses</div>
    <div class="card">
        @if(empty($lignes))
            <div class="row"><span class="label">Aucune dépense ce mois.</span></div>
        @else
            @foreach($lignes as $ligne)
                <div class="row">
                    <span class="row-icon">
                        <span class="icone">{{ $ligne['icone'] }}</span>
                        <span class="label">{{ $ligne['label'] }}</span>
                    </span>
                    <span class="amount">{{ number_format($ligne['montant'], 0, ',', ' ') }} FCFA</span>
                </div>
            @endforeach
        @endif
        <div class="row total-dep">
            <span class="label">Total dépenses</span>
            <span class="amount">{{ number_format($totalDepenses, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>

    {{-- Résultat --}}
    <div class="section-label resultat">Résultat du mois</div>
    @if($benefice > 0)
        <div class="result-box benefice">
            <div class="result-title">🟢 Bénéfice du mois</div>
            <div class="result-amount">+{{ number_format($benefice, 0, ',', ' ') }} FCFA</div>
        </div>
    @elseif($benefice < 0)
        <div class="result-box perte">
            <div class="result-title">🔴 Perte du mois</div>
            <div class="result-amount">{{ number_format($benefice, 0, ',', ' ') }} FCFA</div>
        </div>
    @else
        <div class="result-box equilibre">
            <div class="result-title">⚪ Équilibre</div>
            <div class="result-amount">0 FCFA</div>
        </div>
    @endif

    <div class="footer">
        Bilan généré le {{ now()->format('d/m/Y') }} · {{ $boulangerieName }}
    </div>

</body>
</html>
