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

    {{-- Date --}}
    <div>
        <label for="date_vente" style="display: block; margin-bottom: 8px; font-weight: 700; color: #172033;">
            Date de la vente *
        </label>
        <input type="date" id="date_vente" name="date_vente"
            value="{{ old('date_vente', date('Y-m-d')) }}"
            style="width: 100%; padding: 10px 12px; border: 1px solid #d9dee7; border-radius: 6px; font-size: 14px;"
            required>
    </div>

    {{-- Lignes produits --}}
    <div style="border: 1px solid #d9dee7; border-radius: 8px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin: 0; color: #172033; font-size: 15px; font-weight: 700;">Produits vendus *</h3>
            <button type="button" id="btn-add-produit"
                style="padding: 6px 14px; background: #0f766e; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 700;">
                + Ajouter un produit
            </button>
        </div>

        <div id="vente-rows">
            {{-- rows injected by JS --}}
        </div>

        {{-- Total auto-calculé --}}
        <div style="margin-top: 12px; padding: 12px; background: #f0f5f4; border-radius: 6px;">
            <span style="font-size: 13px; color: #667085;">Total calculé :</span>
            <strong id="total-display" style="font-size: 18px; color: #172033; margin-left: 8px;">0 F</strong>
        </div>
    </div>

    <div style="display: flex; gap: 12px;">
        <button type="submit"
            style="padding: 10px 16px; background: #0f766e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">
            Enregistrer la vente
        </button>
        <a href="{{ route('ventes.index') }}"
            style="padding: 10px 16px; background: #f0f0f0; color: #172033; border: 1px solid #d9dee7; border-radius: 6px; text-decoration: none; font-weight: 700;">
            Annuler
        </a>
    </div>
</div>

<script>
const PRODUITS_VENTE = @json($produits->map(fn($p) => ['id' => $p->id, 'nom' => $p->nom, 'prix' => (float) $p->prix]));
let venteRowIndex = 0;

function buildSelectOptionsVente(selectedId) {
    let opts = '<option value="">-- Produit --</option>';
    PRODUITS_VENTE.forEach(p => {
        const sel = (selectedId && selectedId == p.id) ? 'selected' : '';
        opts += `<option value="${p.id}" data-prix="${p.prix}" ${sel}>${p.nom} (${p.prix.toLocaleString('fr-FR')} F)</option>`;
    });
    return opts;
}

function addVenteRow(produitId = '', qte = '', prix = '') {
    const i = venteRowIndex++;
    const row = document.createElement('div');
    row.className = 'vente-row';
    row.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px; margin-bottom: 10px; align-items: end;';
    row.innerHTML = `
        <div>
            <label style="display:block;font-size:12px;font-weight:700;color:#667085;margin-bottom:4px;">Produit</label>
            <select name="produits[${i}][produit_id]"
                style="width:100%;padding:8px 10px;border:1px solid #d9dee7;border-radius:6px;font-size:13px;"
                class="vente-produit-select">
                ${buildSelectOptionsVente(produitId)}
            </select>
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:700;color:#667085;margin-bottom:4px;">Quantité</label>
            <input type="number" name="produits[${i}][quantite]" value="${qte}" min="1" placeholder="1"
                style="width:100%;padding:8px 10px;border:1px solid #d9dee7;border-radius:6px;font-size:13px;"
                class="vente-qte">
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:700;color:#667085;margin-bottom:4px;">Prix unit. (F)</label>
            <input type="number" name="produits[${i}][prix_unitaire]" value="${prix}" min="0" step="0.01" placeholder="0"
                style="width:100%;padding:8px 10px;border:1px solid #d9dee7;border-radius:6px;font-size:13px;"
                class="vente-prix">
        </div>
        <div>
            <button type="button" class="btn-remove-vente-row"
                style="padding:8px 10px;background:#fee;color:#c33;border:1px solid #c33;border-radius:6px;cursor:pointer;font-size:13px;">✕</button>
        </div>`;
    document.getElementById('vente-rows').appendChild(row);

    // Auto-fill prix when product selected
    const select = row.querySelector('.vente-produit-select');
    const prixInput = row.querySelector('.vente-prix');
    select.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const p = parseFloat(opt.getAttribute('data-prix') || 0);
        if (p > 0) prixInput.value = p;
        recalculateVente();
    });

    recalculateVente();
}

function recalculateVente() {
    let total = 0;
    document.querySelectorAll('.vente-row').forEach(row => {
        const qte  = parseInt(row.querySelector('.vente-qte')?.value || 0);
        const prix = parseFloat(row.querySelector('.vente-prix')?.value || 0);
        total += qte * prix;
    });
    document.getElementById('total-display').textContent = total.toLocaleString('fr-FR') + ' F';
}

document.getElementById('btn-add-produit').addEventListener('click', () => addVenteRow());

document.getElementById('vente-rows').addEventListener('click', e => {
    if (e.target.classList.contains('btn-remove-vente-row')) {
        e.target.closest('.vente-row').remove();
        recalculateVente();
    }
});

document.getElementById('vente-rows').addEventListener('input', recalculateVente);

// Add one empty row by default
addVenteRow();
</script>
