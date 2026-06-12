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
            Nom de la catégorie
        </label>
        <input 
            type="text" 
            id="nom" 
            name="nom" 
            value="{{ old('nom', isset($categorieProduit) ? $categorieProduit->nom : '') }}"
            placeholder="Ex: Pains, Viennoiseries..."
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
    </div>

    <div style="display: flex; gap: 12px;">
        <button type="submit" style="padding: 10px 16px; background: #0f766e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">
            {{ isset($categorieProduit) ? 'Mettre à jour' : 'Créer' }}
        </button>
        <a 
            href="{{ route('categories-produits.index') }}"
            style="padding: 10px 16px; background: #f0f0f0; color: #172033; border: 1px solid #d9dee7; border-radius: 6px; cursor: pointer; text-align: center; text-decoration: none;"
        >
            Annuler
        </a>
    </div>
</div>
