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
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
            <option value="">-- Sélectionner un client --</option>
            @foreach ($clients as $client)
                <option 
                    value="{{ $client->id }}"
                    {{ old('client_abonne_id', isset($consommationAbonne) ? $consommationAbonne->client_abonne_id : '') == $client->id ? 'selected' : '' }}
                >
                    {{ $client->nom }} {{ $client->prenom }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="date_consommation" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Date de consommation *
        </label>
        <input 
            type="date" 
            id="date_consommation" 
            name="date_consommation" 
            value="{{ old('date_consommation', isset($consommationAbonne) ? $consommationAbonne->date_consommation->format('Y-m-d') : '') }}"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
    </div>

    <div>
        <label for="quantite" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Quantité *
        </label>
        <input 
            type="number" 
            id="quantite" 
            name="quantite" 
            value="{{ old('quantite', isset($consommationAbonne) ? $consommationAbonne->quantite : '') }}"
            placeholder="1"
            min="1"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
    </div>

    <div style="display: flex; gap: 12px;">
        <button type="submit" style="padding: 10px 16px; background: #0f766e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">
            {{ isset($consommationAbonne) ? 'Mettre à jour' : 'Créer' }}
        </button>
        <a 
            href="{{ isset($consommationAbonne) ? route('consommations-abonnes.show', $consommationAbonne) : route('consommations-abonnes.index') }}"
            style="padding: 10px 16px; background: #f0f0f0; color: #172033; border: 1px solid #d9dee7; border-radius: 6px; cursor: pointer; text-align: center; text-decoration: none;"
        >
            Annuler
        </a>
    </div>
</div>
