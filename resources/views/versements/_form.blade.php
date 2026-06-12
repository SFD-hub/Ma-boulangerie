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
        <label for="livreur_id" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Livreur *
        </label>
        <select 
            id="livreur_id" 
            name="livreur_id"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
            <option value="">-- Sélectionner un livreur --</option>
            @foreach ($livreurs as $livreur)
                <option 
                    value="{{ $livreur->id }}"
                    {{ old('livreur_id', isset($versement) ? $versement->livreur_id : '') == $livreur->id ? 'selected' : '' }}
                >
                    {{ $livreur->prenom }} {{ $livreur->nom }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="date_versement" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Date de versement *
        </label>
        <input 
            type="date" 
            id="date_versement" 
            name="date_versement" 
            value="{{ old('date_versement', isset($versement) ? $versement->date_versement->format('Y-m-d') : '') }}"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
    </div>

    <div>
        <label for="montant_verse" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Montant versé (€) *
        </label>
        <input 
            type="number" 
            id="montant_verse" 
            name="montant_verse" 
            value="{{ old('montant_verse', isset($versement) ? $versement->montant_verse : '') }}"
            placeholder="0.00"
            step="0.01"
            min="0.01"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; font-family: Arial, sans-serif;"
            required
        >
    </div>

    <div style="display: flex; gap: 12px;">
        <button type="submit" style="padding: 10px 16px; background: #0f766e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">
            {{ isset($versement) ? 'Mettre à jour' : 'Créer' }}
        </button>
        <a 
            href="{{ isset($versement) ? route('versements.show', $versement) : route('versements.index') }}"
            style="padding: 10px 16px; background: #f0f0f0; color: #172033; border: 1px solid #d9dee7; border-radius: 6px; cursor: pointer; text-align: center; text-decoration: none;"
        >
            Annuler
        </a>
    </div>
</div>
