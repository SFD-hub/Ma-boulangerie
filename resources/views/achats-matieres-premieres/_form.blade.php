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
        <label for="matiere_premiere_id" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Matière première *
        </label>
        <select 
            id="matiere_premiere_id" 
            name="matiere_premiere_id"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
            <option value="">-- Sélectionner une matière première --</option>
            @foreach ($matieresPremières as $matiere)
                <option 
                    value="{{ $matiere->id }}"
                    {{ old('matiere_premiere_id', isset($achatMatierePremiere) ? $achatMatierePremiere->matiere_premiere_id : '') == $matiere->id ? 'selected' : '' }}
                >
                    {{ $matiere->nom }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="quantite" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Quantité (unités) *
        </label>
        <input 
            type="number" 
            id="quantite" 
            name="quantite" 
            value="{{ old('quantite', isset($achatMatierePremiere) ? $achatMatierePremiere->quantite : '') }}"
            placeholder="0"
            min="1"
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
            value="{{ old('montant', isset($achatMatierePremiere) ? $achatMatierePremiere->montant : '') }}"
            placeholder="0.00"
            step="0.01"
            min="0.01"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
    </div>

    <div>
        <label for="date_achat" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Date d'achat *
        </label>
        <input 
            type="date" 
            id="date_achat" 
            name="date_achat" 
            value="{{ old('date_achat', isset($achatMatierePremiere) ? $achatMatierePremiere->date_achat->format('Y-m-d') : '') }}"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
    </div>

    <div style="display: flex; gap: 12px;">
        <button type="submit" style="padding: 10px 16px; background: #0f766e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">
            {{ isset($achatMatierePremiere) ? 'Mettre à jour' : 'Créer' }}
        </button>
        <a 
            href="{{ isset($achatMatierePremiere) ? route('achats-matieres-premieres.show', $achatMatierePremiere) : route('achats-matieres-premieres.index') }}"
            style="padding: 10px 16px; background: #f0f0f0; color: #172033; border: 1px solid #d9dee7; border-radius: 6px; cursor: pointer; text-align: center; text-decoration: none;"
        >
            Annuler
        </a>
    </div>
</div>
