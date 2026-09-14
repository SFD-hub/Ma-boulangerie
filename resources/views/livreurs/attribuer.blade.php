@extends('layouts.app')

@section('title', 'Attribuer des pains')

@section('content')

    {{-- ── En-tête ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('livreurs.show', $livreur) }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Attribuer des pains</span>
        <div style="width:36px"></div>
    </div>

    {{-- ── Erreurs de validation ── --}}
    @if($errors->any())
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:12px;padding:14px 16px;margin-bottom:16px">
            @foreach($errors->all() as $msg)
                <div style="font-size:13px;color:#EF4444;padding:2px 0">{{ $msg }}</div>
            @endforeach
        </div>
    @endif

    <div style="background:#FFFFFF;border-radius:18px;padding:20px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07)">

        <form method="POST" action="{{ route('livreurs.attribuer', $livreur) }}">
            @csrf

            {{-- Livreur (affichage) --}}
            <div style="margin-bottom:18px">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Livreur</label>
                <div style="background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:13px 16px;font-size:15px;font-weight:600;color:#111827;display:flex;align-items:center;gap:10px">
                    <div style="width:32px;height:32px;border-radius:8px;background:#F97316;color:#FFFFFF;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0">
                        {{ strtoupper(substr($livreur->prenom, 0, 1)) }}
                    </div>
                    {{ $livreur->prenom }} {{ $livreur->nom }}
                </div>
            </div>

            {{-- Date --}}
            <div style="margin-bottom:18px">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Date *</label>
                <input type="date" name="date_distribution"
                       value="{{ old('date_distribution', date('Y-m-d')) }}"
                       max="{{ date('Y-m-d') }}" required
                       style="width:100%;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;font-size:15px;color:#111827;box-sizing:border-box;outline:none;-webkit-appearance:none">
            </div>

            {{-- Produit --}}
            <div style="margin-bottom:18px">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Produit *</label>
                <select name="produit_id" required
                        style="width:100%;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;font-size:15px;color:#111827;box-sizing:border-box;outline:none">
                    @foreach($produits as $produit)
                        <option value="{{ $produit->id }}" {{ old('produit_id', $defaultProduitId) == $produit->id ? 'selected' : '' }}>
                            {{ $produit->nom }}
                        </option>
                    @endforeach
                </select>
                @if($produits->isEmpty())
                    <p style="margin:8px 0 0;font-size:12px;color:#EF4444">
                        Aucun produit configuré. <a href="{{ route('produits.create') }}">Créez-en un</a> avant de continuer.
                    </p>
                @endif
            </div>

            {{-- Nombre de pains --}}
            <div style="margin-bottom:18px">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Nombre de pains *</label>
                <input type="number" name="nombre_pains" min="1"
                       value="{{ old('nombre_pains') }}" placeholder="Ex: 100" required
                       oninput="calcMontant()"
                       style="width:100%;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;font-size:15px;color:#111827;box-sizing:border-box;outline:none">
            </div>

            {{-- Prix du pain --}}
            <div style="margin-bottom:18px">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Prix du pain (FCFA) *</label>
                <input type="number" name="prix_pain" min="1"
                       value="{{ old('prix_pain', $prixPain ?: '') }}"
                       placeholder="Ex: 100" required
                       oninput="calcMontant()"
                       style="width:100%;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;font-size:15px;color:#111827;box-sizing:border-box;outline:none">
            </div>

            {{-- Aperçu montant attendu --}}
            <div id="montantPreview" style="display:none;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:13px 16px;margin-bottom:18px;justify-content:space-between;align-items:center">
                <span style="font-size:13px;color:#374151;font-weight:600">Montant attendu</span>
                <span id="montantVal" style="font-size:15px;font-weight:800;color:#059669">—</span>
            </div>

            <button type="submit"
                    style="width:100%;background:#F97316;color:#FFFFFF;border:none;border-radius:14px;padding:16px 20px;font-size:16px;font-weight:700;cursor:pointer">
                Attribuer
            </button>

        </form>
    </div>

    <script>
        function calcMontant() {
            const pains = parseInt(document.querySelector('input[name="nombre_pains"]')?.value || 0);
            const prix  = parseInt(document.querySelector('input[name="prix_pain"]')?.value || 0);
            const preview = document.getElementById('montantPreview');
            const val     = document.getElementById('montantVal');
            if (pains > 0 && prix > 0) {
                val.textContent    = (pains * prix).toLocaleString('fr-FR') + ' FCFA';
                preview.style.display = 'flex';
            } else {
                preview.style.display = 'none';
            }
        }
        document.addEventListener('DOMContentLoaded', calcMontant);
    </script>

@endsection
