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
        <label for="nom" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Nom de la matière première *
        </label>
        <input 
            type="text" 
            id="nom" 
            name="nom" 
            value="{{ old('nom', isset($matierePremiere) ? $matierePremiere->nom : '') }}"
            placeholder="Ex: Farine, Sucre, Levure..."
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
    </div>

    <div>
        <label for="stock_actuel" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Stock actuel (unités) *
        </label>
        <input 
            type="number" 
            id="stock_actuel" 
            name="stock_actuel" 
            value="{{ old('stock_actuel', isset($matierePremiere) ? $matierePremiere->stock_actuel : '') }}"
            placeholder="0"
            min="0"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
    </div>

    <div>
        <label for="seuil_alerte" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Seuil d'alerte (unités) *
        </label>
        <input 
            type="number" 
            id="seuil_alerte" 
            name="seuil_alerte" 
            value="{{ old('seuil_alerte', isset($matierePremiere) ? $matierePremiere->seuil_alerte : '') }}"
            placeholder="0"
            min="0"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
        <p style="margin: 8px 0 0; font-size: 12px; color: #667085;">
            Stock minimum avant d'afficher une alerte
        </p>
    </div>

    <div style="display: flex; gap: 12px;">
        <button type="submit" style="padding: 10px 16px; background: #0f766e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">
            {{ isset($matierePremiere) ? 'Mettre à jour' : 'Créer' }}
        </button>
        <a 
            href="{{ route('matieres-premieres.index') }}"
            style="padding: 10px 16px; background: #f0f0f0; color: #172033; border: 1px solid #d9dee7; border-radius: 6px; cursor: pointer; text-align: center; text-decoration: none;"
        >
            Annuler
        </a>
    </div>
</div>
