<div>
    <label>Libellé</label>
    <input type="text" name="libelle"
           value="{{ old('libelle', isset($depense) ? $depense->libelle : '') }}">
</div>

<div>
    <label>Catégorie</label>
    <input type="text" name="categorie"
           value="{{ old('categorie', isset($depense) ? $depense->categorie : '') }}">
</div>

<div>
    <label>Montant</label>
    <input type="number" step="0.01" name="montant"
           value="{{ old('montant', isset($depense) ? $depense->montant : '') }}">
</div>

<div>
    <label>Date</label>
    <input type="date" name="date_depense"
           value="{{ old('date_depense',
                isset($depense)
                    ? $depense->date_depense->format('Y-m-d')
                    : '') }}">
</div>

<button type="submit">Enregistrer</button>