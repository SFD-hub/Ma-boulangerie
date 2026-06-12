@if ($errors->any())
    <div style="background: #fee; border: 1px solid #c33; padding: 16px; border-radius: 6px; margin-bottom: 16px; color: #c33;">
        <strong>Erreurs de validation :</strong>
        <ul style="margin: 8px 0 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div style="display: grid; gap: 20px;">
    <div>
        <label for="facture_id" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Facture *
        </label>
        <select 
            id="facture_id" 
            name="facture_id" 
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
            <option value="">-- Sélectionner une facture --</option>
            @foreach ($factures as $facture)
                <option 
                    value="{{ $facture->id }}"
                    {{ old('facture_id', isset($paiementFacture) ? $paiementFacture->facture_id : '') == $facture->id ? 'selected' : '' }}
                >
                    {{ $facture->clientAbonne->nom }} {{ $facture->clientAbonne->prenom }} - {{ $facture->mois }}/{{ $facture->annee }} - {{ number_format($facture->montant_total, 2, ',', ' ') }} €
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="date_paiement" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Date de paiement *
        </label>
        <input 
            type="date" 
            id="date_paiement" 
            name="date_paiement" 
            value="{{ old('date_paiement', isset($paiementFacture) ? $paiementFacture->date_paiement->format('Y-m-d') : '') }}"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
    </div>

    <div>
        <label for="montant" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Montant (€) *
        </label>
        <input 
            type="number" 
            id="montant" 
            name="montant" 
            value="{{ old('montant', isset($paiementFacture) ? $paiementFacture->montant : '') }}"
            placeholder="0.00"
            step="0.01"
            min="0.01"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
    </div>

    <div style="display: flex; gap: 12px;">
        <button type="submit" style="padding: 10px 16px; background: #0f766e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">
            {{ isset($paiementFacture) ? 'Mettre à jour' : 'Créer' }}
        </button>
        <a 
            href="{{ isset($paiementFacture) ? route('paiements-factures.show', $paiementFacture) : route('paiements-factures.index') }}"
            style="padding: 10px 16px; background: #f0f0f0; color: #172033; border: 1px solid #d9dee7; border-radius: 6px; cursor: pointer; text-align: center; text-decoration: none;"
        >
            Annuler
        </a>
    </div>
</div>
