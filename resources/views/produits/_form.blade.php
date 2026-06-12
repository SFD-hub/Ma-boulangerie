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
            Nom du produit *
        </label>
        <input 
            type="text" 
            id="nom" 
            name="nom" 
            value="{{ old('nom', isset($produit) ? $produit->nom : '') }}"
            placeholder="Ex: Pain blanc 500g"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
    </div>

    <div>
        <label for="description" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Description
        </label>
        <textarea 
            id="description" 
            name="description"
            placeholder="Description du produit (optionnel)"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif; min-height: 100px; resize: vertical;"
        >{{ old('description', isset($produit) ? $produit->description : '') }}</textarea>
    </div>

    <div>
        <label for="prix" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Prix (€) *
        </label>
        <input 
            type="number" 
            id="prix" 
            name="prix" 
            value="{{ old('prix', isset($produit) ? $produit->prix : '') }}"
            placeholder="0.00"
            step="0.01"
            min="0.01"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
    </div>

    <div>
        <label for="categorie_produit_id" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Catégorie *
        </label>
        <select 
            id="categorie_produit_id" 
            name="categorie_produit_id"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
            <option value="">-- Sélectionner une catégorie --</option>
            @foreach ($categories as $categorie)
                <option 
                    value="{{ $categorie->id }}"
                    {{ old('categorie_produit_id', isset($produit) ? $produit->categorie_produit_id : '') == $categorie->id ? 'selected' : '' }}
                >
                    {{ $categorie->nom }}
                </option>
            @endforeach
        </select>
    </div>

    <div style="display: flex; gap: 12px;">
        <button type="submit" style="padding: 10px 16px; background: #0f766e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">
            {{ isset($produit) ? 'Mettre à jour' : 'Créer' }}
        </button>
        <a 
            href="{{ isset($produit) ? route('produits.show', $produit) : route('produits.index') }}"
            style="padding: 10px 16px; background: #f0f0f0; color: #172033; border: 1px solid #d9dee7; border-radius: 6px; cursor: pointer; text-align: center; text-decoration: none;"
        >
            Annuler
        </a>
    </div>
</div>
