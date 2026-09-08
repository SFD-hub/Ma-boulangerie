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

    {{-- Livreur (lecture seule — non modifiable) --}}
    <div>
        <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Livreur
        </label>
        <div style="padding: 10px 12px; background: #f0f0f0; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px; color: #172033;">
            {{ $distribution->livreur->prenom }} {{ $distribution->livreur->nom }}
        </div>
    </div>

    {{-- Produit --}}
    <div>
        <label for="produit_id" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Produit *
        </label>
        <select id="produit_id" name="produit_id"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px;"
            required>
            <option value="">-- Sélectionner --</option>
            @foreach ($produits as $produit)
                <option value="{{ $produit->id }}"
                    {{ old('produit_id', $distribution->produit_id) == $produit->id ? 'selected' : '' }}>
                    {{ $produit->nom }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Date --}}
    <div>
        <label for="date_distribution" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Date de distribution *
        </label>
        <input type="date" id="date_distribution" name="date_distribution"
            value="{{ old('date_distribution', $distribution->date_distribution->format('Y-m-d')) }}"
            max="{{ date('Y-m-d') }}"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px;"
            required>
    </div>

    {{-- Nombre de pains --}}
    <div>
        <label for="nombre_pains" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Nombre de pains *
        </label>
        <input type="number" id="nombre_pains" name="nombre_pains" min="1"
            value="{{ old('nombre_pains', $distribution->nombre_pains) }}"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px;"
            required>
    </div>

    {{-- Prix du pain --}}
    <div>
        <label for="prix_pain" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Prix du pain (FCFA) *
        </label>
        <input type="number" id="prix_pain" name="prix_pain" min="1"
            value="{{ old('prix_pain', $distribution->prix_pain) }}"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px;"
            required>
    </div>

    <div style="display: flex; gap: 12px;">
        <button type="submit"
            style="padding: 10px 16px; background: #0f766e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">
            Mettre à jour
        </button>
        <a href="{{ route('distributions.show', $distribution) }}"
            style="padding: 10px 16px; background: #f0f0f0; color: #172033; border: 1px solid #d9dee7; border-radius: 6px; text-decoration: none; font-weight: 700;">
            Annuler
        </a>
    </div>
</div>
