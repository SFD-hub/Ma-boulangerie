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
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
            <label for="nom" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
                Nom *
            </label>
            <input 
                type="text" 
                id="nom" 
                name="nom" 
                value="{{ old('nom', isset($livreur) ? $livreur->nom : '') }}"
                placeholder="Dupont"
                style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
                required
            >
        </div>

        <div>
            <label for="prenom" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
                Prénom *
            </label>
            <input 
                type="text" 
                id="prenom" 
                name="prenom" 
                value="{{ old('prenom', isset($livreur) ? $livreur->prenom : '') }}"
                placeholder="Jean"
                style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
                required
            >
        </div>
    </div>

    <div>
        <label for="telephone" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Téléphone *
        </label>
        <input 
            type="tel" 
            id="telephone" 
            name="telephone" 
            value="{{ old('telephone', isset($livreur) ? $livreur->telephone : '') }}"
            placeholder="+33 6 12 34 56 78"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
    </div>

    <div>
        <label for="adresse" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Adresse
        </label>
        <textarea 
            id="adresse" 
            name="adresse"
            placeholder="123 rue du Pain, 75000 Paris"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif; min-height: 80px; resize: vertical;"
        >{{ old('adresse', isset($livreur) ? $livreur->adresse : '') }}</textarea>
    </div>

    <div style="display: flex; gap: 12px;">
        <button type="submit" style="padding: 10px 16px; background: #0f766e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">
            {{ isset($livreur) ? 'Mettre à jour' : 'Créer' }}
        </button>
        <a 
            href="{{ isset($livreur) ? route('livreurs.show', $livreur) : route('livreurs.index') }}"
            style="padding: 10px 16px; background: #f0f0f0; color: #172033; border: 1px solid #d9dee7; border-radius: 6px; cursor: pointer; text-align: center; text-decoration: none;"
        >
            Annuler
        </a>
    </div>
</div>
