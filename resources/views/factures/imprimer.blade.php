<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $facture->periodeLabel() }} — {{ $facture->clientAbonne->prenom }} {{ $facture->clientAbonne->nom }}</title>
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

        /* ── En-tête boulangerie ── */
        .header {
            text-align: center;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 2px solid #F97316;
        }
        .boulangerie-name {
            font-size: 22px;
            font-weight: 800;
            color: #F97316;
            margin-bottom: 4px;
        }
        .facture-title {
            font-size: 14px;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        /* ── Infos client ── */
        .section { margin-bottom: 24px; }
        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 8px;
        }
        .client-name { font-size: 18px; font-weight: 700; color: #111827; }
        .client-phone { font-size: 14px; color: #6B7280; margin-top: 3px; }

        /* ── Tableau détails ── */
        .detail-table { width: 100%; border-collapse: collapse; }
        .detail-table td {
            padding: 10px 0;
            border-bottom: 1px solid #F3F4F6;
            font-size: 14px;
        }
        .detail-table td:first-child { color: #6B7280; }
        .detail-table td:last-child { font-weight: 700; text-align: right; color: #111827; }

        /* ── Montant total ── */
        .total-box {
            background: #FFF7ED;
            border: 2px solid #F97316;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 24px;
            margin-bottom: 24px;
        }
        .total-label { font-size: 15px; font-weight: 700; color: #374151; }
        .total-amount { font-size: 22px; font-weight: 900; color: #F97316; }

        /* ── Footer ── */
        .footer {
            text-align: center;
            font-size: 12px;
            color: #9CA3AF;
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #F3F4F6;
        }

        /* ── Bouton imprimer (masqué à l'impression) ── */
        .print-btn {
            display: block;
            margin: 0 auto 24px;
            background: #F97316;
            color: #FFFFFF;
            border: none;
            border-radius: 12px;
            padding: 12px 28px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }

        @media print {
            .print-btn { display: none !important; }
            body { padding: 20px; }
        }
    </style>
</head>
<body onload="window.print()">

    <button class="print-btn" onclick="window.print()">
        🖨️ Imprimer / Enregistrer PDF
    </button>

    {{-- ── En-tête ── --}}
    <div class="header">
        <div class="boulangerie-name">
            {{ $boulangerie?->nom ?? 'Ma Boulangerie' }}
        </div>
        <div class="facture-title">Facture abonné</div>
    </div>

    {{-- ── Client ── --}}
    <div class="section">
        <div class="section-title">Abonné</div>
        <div class="client-name">{{ $facture->clientAbonne->prenom }} {{ $facture->clientAbonne->nom }}</div>
        @if($facture->clientAbonne->telephone)
            <div class="client-phone">{{ $facture->clientAbonne->telephone }}</div>
        @endif
    </div>

    {{-- ── Détails ── --}}
    <div class="section">
        <div class="section-title">Détails</div>
        <table class="detail-table">
            <tr>
                <td>Période</td>
                <td>{{ $facture->periodeLabel() }}</td>
            </tr>
            <tr>
                <td>Pains consommés</td>
                <td>{{ $facture->quantite_totale }} pains</td>
            </tr>
            @if($facture->prix_unitaire)
            <tr>
                <td>Prix unitaire</td>
                <td>{{ number_format($facture->prix_unitaire, 0, ',', ' ') }} FCFA / pain</td>
            </tr>
            @endif
            <tr>
                <td>Date de génération</td>
                <td>{{ $facture->date_facture->format('d/m/Y') }}</td>
            </tr>
            @if($facture->statut === 'payee' && $facture->date_paiement)
            <tr>
                <td>Date de paiement</td>
                <td>{{ $facture->date_paiement->format('d/m/Y') }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- ── Montant total ── --}}
    <div class="total-box">
        <span class="total-label">Montant total</span>
        <span class="total-amount">{{ number_format($facture->montant_total, 0, ',', ' ') }} FCFA</span>
    </div>

    <div class="footer">
        Facture générée le {{ $facture->date_facture->format('d/m/Y') }}
        · {{ $boulangerie?->nom ?? 'Ma Boulangerie' }}
    </div>

</body>
</html>
