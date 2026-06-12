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

    {{-- Livreur --}}
    <div>
        <label for="livreur_id" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Livreur *
        </label>
        <select id="livreur_id" name="livreur_id"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px;"
            required>
            <option value="">-- Sélectionner un livreur --</option>
            @foreach ($livreurs as $livreur)
                <option value="{{ $livreur->id }}"
                    {{ old('livreur_id', isset($distribution) ? $distribution->livreur_id : '') == $livreur->id ? 'selected' : '' }}>
                    {{ $livreur->prenom }} {{ $livreur->nom }}
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
            value="{{ old('date_distribution', isset($distribution) ? $distribution->date_distribution->format('Y-m-d') : '') }}"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px;"
            required>
    </div>

    {{-- ── Section lignes produits ── --}}
    <div style="border: 1px solid #d9dee7; border-radius: 8px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin: 0; color: #172033; font-size: 15px; font-weight: 700;">Produits distribués</h3>
            <button type="button" id="btn-add-produit"
                style="padding: 6px 14px; background: #0f766e; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 700;">
                + Ajouter un produit
            </button>
        </div>

        <div id="produits-rows">
            @if (isset($distribution) && $distribution->detailDistributions->isNotEmpty())
                @foreach ($distribution->detailDistributions as $i => $detail)
                    <div class="produit-row" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px; margin-bottom: 10px; align-items: end;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #667085; margin-bottom: 4px;">Produit</label>
                            <select name="produits[{{ $i }}][produit_id]"
                                style="width: 100%; padding: 8px 10px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 13px;">
                                <option value="">-- Produit --</option>
                                @foreach ($produits as $produit)
                                    <option value="{{ $produit->id }}" data-prix="{{ $produit->prix }}"
                                        {{ $detail->produit_id == $produit->id ? 'selected' : '' }}>
                                        {{ $produit->nom }} ({{ number_format($produit->prix, 0, ',', ' ') }} F)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #667085; margin-bottom: 4px;">Qté attribuée</label>
                            <input type="number" name="produits[{{ $i }}][quantite_attribuee]"
                                value="{{ $detail->quantite_attribuee }}" min="1"
                                style="width: 100%; padding: 8px 10px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 13px;"
                                class="qte-attr">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #667085; margin-bottom: 4px;">Qté retournée</label>
                            <input type="number" name="produits[{{ $i }}][quantite_retournee]"
                                value="{{ $detail->quantite_retournee }}" min="0"
                                style="width: 100%; padding: 8px 10px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 13px;"
                                class="qte-ret">
                        </div>
                        <div>
                            <button type="button" class="btn-remove-row"
                                style="padding: 8px 10px; background: #fee; color: #c33; border: 1px solid #c33; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                ✕
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Récapitulatif auto-calculé --}}
        <div id="montant-preview" style="margin-top: 12px; padding: 12px; background: #f0f5f4; border-radius: 6px; display: none;">
            <span style="font-size: 13px; color: #667085;">Montant calculé :</span>
            <strong id="montant-calcule-display" style="font-size: 16px; color: #172033; margin-left: 8px;">0 F</strong>
        </div>
    </div>

    {{-- Montant attendu (manuel si pas de lignes, calculé sinon) --}}
    <div>
        <label for="montant_attendu" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Montant attendu *
            <span id="montant-auto-note" style="font-size: 12px; color: #0f766e; font-weight: 400; margin-left: 6px; display: none;">
                (calculé automatiquement depuis les produits)
            </span>
        </label>
        <input type="number" id="montant_attendu" name="montant_attendu"
            value="{{ old('montant_attendu', isset($distribution) ? $distribution->montant_attendu : '') }}"
            placeholder="0" step="0.01" min="0"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px;"
            required>
    </div>

    <div style="display: flex; gap: 12px;">
        <button type="submit"
            style="padding: 10px 16px; background: #0f766e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">
            {{ isset($distribution) ? 'Mettre à jour' : 'Créer' }}
        </button>
        <a href="{{ isset($distribution) ? route('distributions.show', $distribution) : route('distributions.index') }}"
            style="padding: 10px 16px; background: #f0f0f0; color: #172033; border: 1px solid #d9dee7; border-radius: 6px; text-decoration: none; font-weight: 700;">
            Annuler
        </a>
    </div>
</div>

{{-- Données produits en JSON pour JS --}}
<script>
const PRODUITS = @json($produits->map(fn($p) => ['id' => $p->id, 'nom' => $p->nom, 'prix' => (float) $p->prix]));
let rowIndex = {{ isset($distribution) ? $distribution->detailDistributions->count() : 0 }};

function buildSelectOptions(selectedId) {
    let opts = '<option value="">-- Produit --</option>';
    PRODUITS.forEach(p => {
        const sel = (selectedId && selectedId == p.id) ? 'selected' : '';
        opts += `<option value="${p.id}" data-prix="${p.prix}" ${sel}>${p.nom} (${p.prix.toLocaleString('fr-FR')} F)</option>`;
    });
    return opts;
}

function addRow(produitId = '', qteAttr = '', qteRet = 0) {
    const i = rowIndex++;
    const row = document.createElement('div');
    row.className = 'produit-row';
    row.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px; margin-bottom: 10px; align-items: end;';
    row.innerHTML = `
        <div>
            <label style="display:block;font-size:12px;font-weight:700;color:#667085;margin-bottom:4px;">Produit</label>
            <select name="produits[${i}][produit_id]" style="width:100%;padding:8px 10px;border:1px solid #d9dee7;border-radius:6px;font-size:13px;">
                ${buildSelectOptions(produitId)}
            </select>
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:700;color:#667085;margin-bottom:4px;">Qté attribuée</label>
            <input type="number" name="produits[${i}][quantite_attribuee]" value="${qteAttr}" min="1"
                style="width:100%;padding:8px 10px;border:1px solid #d9dee7;border-radius:6px;font-size:13px;" class="qte-attr">
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:700;color:#667085;margin-bottom:4px;">Qté retournée</label>
            <input type="number" name="produits[${i}][quantite_retournee]" value="${qteRet}" min="0"
                style="width:100%;padding:8px 10px;border:1px solid #d9dee7;border-radius:6px;font-size:13px;" class="qte-ret">
        </div>
        <div>
            <button type="button" class="btn-remove-row"
                style="padding:8px 10px;background:#fee;color:#c33;border:1px solid #c33;border-radius:6px;cursor:pointer;font-size:13px;">✕</button>
        </div>`;
    document.getElementById('produits-rows').appendChild(row);
    recalculate();
}

function recalculate() {
    const rows = document.querySelectorAll('.produit-row');
    let total = 0;
    let hasRows = rows.length > 0;

    rows.forEach(row => {
        const select = row.querySelector('select');
        const attrInput = row.querySelector('.qte-attr');
        const retInput = row.querySelector('.qte-ret');
        if (!select || !attrInput) return;

        const option = select.options[select.selectedIndex];
        const prix = option ? parseFloat(option.getAttribute('data-prix') || 0) : 0;
        const attr = parseInt(attrInput.value || 0);
        const ret  = parseInt(retInput ? retInput.value || 0 : 0);
        const vendu = Math.max(0, attr - ret);
        total += vendu * prix;
    });

    const montantInput = document.getElementById('montant_attendu');
    const preview = document.getElementById('montant-preview');
    const display = document.getElementById('montant-calcule-display');
    const note = document.getElementById('montant-auto-note');

    if (hasRows) {
        montantInput.value = total.toFixed(2);
        montantInput.readOnly = true;
        montantInput.style.background = '#f0f5f4';
        display.textContent = total.toLocaleString('fr-FR') + ' F';
        preview.style.display = 'block';
        note.style.display = 'inline';
    } else {
        montantInput.readOnly = false;
        montantInput.style.background = '';
        preview.style.display = 'none';
        note.style.display = 'none';
    }
}

document.getElementById('btn-add-produit').addEventListener('click', () => addRow());

document.getElementById('produits-rows').addEventListener('click', e => {
    if (e.target.classList.contains('btn-remove-row')) {
        e.target.closest('.produit-row').remove();
        recalculate();
    }
});

document.getElementById('produits-rows').addEventListener('input', recalculate);
document.getElementById('produits-rows').addEventListener('change', recalculate);

// Recalcul initial si des lignes existent déjà (mode édition)
recalculate();
</script>
