@extends('layouts.app')

@section('title', 'Modifier dépense')

@section('content')

    <a href="{{ route('depenses.index') }}" class="back-link">← Dépenses</a>

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-title">Modifier une dépense</div>

        <form method="POST" action="{{ route('depenses.update', $depense) }}">
            @csrf @method('PUT')

            {{-- ── Aperçu icône catégorie ── --}}
            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;margin-bottom:20px">
                <div id="cat-icon-wrap">
                    <div id="cat-icon-default" style="width:56px;height:56px;border-radius:14px;background:#F3F4F6;display:none;align-items:center;justify-content:center">
                        <svg width="28" height="28" fill="none" stroke="#D1D5DB" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                        </svg>
                    </div>

                    @foreach($categories as $val => $label)
                        <div class="cat-icon-item" data-cat="{{ $val }}" style="display:none">
                            <x-depense-icone :categorie="$val" :size="56" />
                        </div>
                    @endforeach
                </div>
                <span id="cat-label" style="font-size:12px;font-weight:600;color:#111827"></span>
            </div>

            <div class="form-group">
                <label class="form-label">Type *</label>
                <select class="form-input" name="categorie" id="cat-select" required>
                    @foreach($categories as $val => $label)
                        <option value="{{ $val }}" {{ old('categorie', $depense->categorie) === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Motif de la dépense *</label>
                <input class="form-input" type="text" name="libelle"
                       value="{{ old('libelle', $depense->libelle) }}"
                       required minlength="3">
            </div>

            <div class="form-group">
                <label class="form-label">Montant (FCFA) *</label>
                <input class="form-input" type="number" name="montant" min="1"
                       value="{{ old('montant', $depense->montant) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Date *</label>
                <input class="form-input" type="date" name="date_depense"
                       value="{{ old('date_depense', $depense->date_depense->format('Y-m-d')) }}"
                       max="{{ date('Y-m-d') }}" required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Enregistrer</button>
        </form>
    </div>

    <script>
        const catLabels = @json($categories);

        function updateCatIcon(val) {
            document.querySelectorAll('.cat-icon-item').forEach(el => el.style.display = 'none');
            const defaultIcon = document.getElementById('cat-icon-default');
            const labelEl     = document.getElementById('cat-label');

            if (val && document.querySelector('.cat-icon-item[data-cat="' + val + '"]')) {
                defaultIcon.style.display = 'none';
                document.querySelector('.cat-icon-item[data-cat="' + val + '"]').style.display = 'flex';
                labelEl.textContent = catLabels[val] || '';
            } else {
                defaultIcon.style.display = 'flex';
                labelEl.textContent = '';
            }
        }

        const sel = document.getElementById('cat-select');
        sel.addEventListener('change', () => updateCatIcon(sel.value));
        updateCatIcon(sel.value);
    </script>

@endsection
