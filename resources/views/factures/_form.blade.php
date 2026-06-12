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
        <label for="client_abonne_id" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Client *
        </label>
        <select
            id="client_abonne_id"
            name="client_abonne_id"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px;"
            required
        >
            <option value="">-- Sélectionner un client --</option>
            @foreach ($clients as $client)
                <option
                    value="{{ $client->id }}"
                    {{ old('client_abonne_id', isset($facture) ? $facture->client_abonne_id : '') == $client->id ? 'selected' : '' }}
                >
                    {{ $client->nom }} {{ $client->prenom }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="mois" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Mois *
        </label>
        <input
            type="number"
            id="mois"
            name="mois"
            value="{{ old('mois', isset($facture) ? $facture->mois : '') }}"
            placeholder="1"
            min="1"
            max="12"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px;"
            required
        >
    </div>

    <div>
        <label for="annee" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Année *
        </label>
        <input
            type="number"
            id="annee"
            name="annee"
            value="{{ old('annee', isset($facture) ? $facture->annee : '') }}"
            placeholder="2024"
            min="2000"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px;"
            required
        >
    </div>

    @if (!empty($modeCreation))
    <div>
        <label for="prix_unitaire" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Prix unitaire (F) *
        </label>
        <input
            type="number"
            id="prix_unitaire"
            name="prix_unitaire"
            value="{{ old('prix_unitaire') }}"
            placeholder="0"
            step="0.01"
            min="0.01"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px;"
            required
        >
    </div>
    @endif

    <div>
        <label for="quantite_totale" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Quantité totale
            @if (!empty($modeCreation))
                <span style="font-size: 12px; color: #0f766e; font-weight: 400; margin-left: 6px;">(calculée depuis les consommations)</span>
            @else
                *
            @endif
        </label>
        <input
            type="number"
            id="quantite_totale"
            name="quantite_totale"
            value="{{ old('quantite_totale', isset($facture) ? $facture->quantite_totale : '') }}"
            placeholder="0"
            min="0"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; {{ !empty($modeCreation) ? 'background:#f0f5f4;' : '' }}"
            @if (!empty($modeCreation)) readonly @else required @endif
        >
    </div>

    <div>
        <label for="montant_total" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Montant total (F)
            @if (!empty($modeCreation))
                <span style="font-size: 12px; color: #0f766e; font-weight: 400; margin-left: 6px;">(calculé automatiquement)</span>
            @else
                *
            @endif
        </label>
        <input
            type="number"
            id="montant_total"
            name="montant_total"
            value="{{ old('montant_total', isset($facture) ? $facture->montant_total : '') }}"
            placeholder="0.00"
            step="0.01"
            min="0"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; {{ !empty($modeCreation) ? 'background:#f0f5f4;' : '' }}"
            @if (!empty($modeCreation)) readonly @else required @endif
        >
    </div>

    <div>
        <label for="date_facture" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Date de facture *
        </label>
        <input
            type="date"
            id="date_facture"
            name="date_facture"
            value="{{ old('date_facture', isset($facture) ? $facture->date_facture->format('Y-m-d') : '') }}"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px;"
            required
        >
    </div>

    <div>
        <label for="statut" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Statut *
        </label>
        <select
            id="statut"
            name="statut"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px;"
            required
        >
            <option value="impayee" {{ old('statut', isset($facture) ? $facture->statut : 'impayee') == 'impayee' ? 'selected' : '' }}>Impayée</option>
            <option value="payee" {{ old('statut', isset($facture) ? $facture->statut : '') == 'payee' ? 'selected' : '' }}>Payée</option>
        </select>
    </div>

    <div style="display: flex; gap: 12px;">
        <button type="submit" style="padding: 10px 16px; background: #0f766e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">
            {{ isset($facture) ? 'Mettre à jour' : 'Créer' }}
        </button>
        <a
            href="{{ isset($facture) ? route('factures.show', $facture) : route('factures.index') }}"
            style="padding: 10px 16px; background: #f0f0f0; color: #172033; border: 1px solid #d9dee7; border-radius: 6px; cursor: pointer; text-align: center; text-decoration: none;"
        >
            Annuler
        </a>
    </div>
</div>

@if (!empty($modeCreation))
<script>
function updateFacturePreview() {
    const clientId = document.getElementById('client_abonne_id').value;
    const mois     = document.getElementById('mois').value;
    const annee    = document.getElementById('annee').value;
    const prixEl   = document.getElementById('prix_unitaire');
    const qtEl     = document.getElementById('quantite_totale');
    const mtEl     = document.getElementById('montant_total');

    if (!clientId || !mois || !annee) {
        qtEl.value = '';
        mtEl.value = '';
        return;
    }

    const key  = annee + '-' + String(mois).padStart(2, '0');
    const data = (typeof CONSOMMATIONS !== 'undefined') ? CONSOMMATIONS[clientId] : null;
    const qt   = (data && data[key]) ? parseInt(data[key]) : 0;

    qtEl.value = qt;

    const prix = prixEl ? (parseFloat(prixEl.value) || 0) : 0;
    mtEl.value = (qt * prix).toFixed(2);
}

['client_abonne_id', 'mois', 'annee', 'prix_unitaire'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) {
        el.addEventListener('change', updateFacturePreview);
        el.addEventListener('input', updateFacturePreview);
    }
});
</script>
@endif
