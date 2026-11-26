@extends('layouts.app')

@section('title', 'Modifier le versement')

@section('content')

    @php
        $retour = route('livreurs.distributions', $livreur);
        $champ  = 'width:100%;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;font-size:15px;color:#111827;box-sizing:border-box;outline:none';
        $petit  = 'width:100%;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:12px 14px;font-size:15px;color:#111827;box-sizing:border-box;outline:none';
        $entiers = old('entiers_invendus', $distribution->entiers_invendus ?? $distribution->nombre_invendus ?? 0);
        $moities = old('moities_invendus', $distribution->moities_invendus ?? 0);
        $tiers   = old('tiers_invendus', $distribution->tiers_invendus ?? 0);
    @endphp

    {{-- ── En-tête ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ $retour }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Modifier le versement</span>
        <div style="width:36px"></div>
    </div>

    @if($errors->any())
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:12px;padding:14px 16px;margin-bottom:16px">
            @foreach($errors->all() as $msg)
                <div style="font-size:13px;color:#EF4444;padding:2px 0">{{ $msg }}</div>
            @endforeach
        </div>
    @endif

    <div style="background:#FFFFFF;border-radius:18px;padding:20px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07)">

        <div style="background:#FFF7ED;border:1px solid #F97316;border-radius:12px;padding:14px 16px;margin-bottom:18px">
            <div style="font-size:15px;font-weight:700;color:#111827">{{ $livreur->prenom }} {{ $livreur->nom }}</div>
            @if($distribution)
                <div style="font-size:12px;color:#6B7280;margin-top:2px">
                    {{ $distribution->produit->nom ?? '—' }} · {{ $distribution->date_distribution->format('d/m/Y') }}
                    · {{ $distribution->pains_confies_texte }}
                    @if($distribution->prix_pain) · {{ number_format($distribution->prix_pain, 0, ',', ' ') }}&nbsp;FCFA/pain @endif
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('versements.update', $versement) }}">
            @csrf @method('PUT')

            @if($restantsModifiables)
                <div style="margin-bottom:18px">
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Restants</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
                        <div>
                            <label style="font-size:12px;color:#6B7280;display:block;margin-bottom:4px">Entiers</label>
                            <input type="number" name="entiers_invendus" min="0" id="invE" value="{{ $entiers }}" oninput="calcAttendu()" style="{{ $petit }}">
                        </div>
                        <div>
                            <label style="font-size:12px;color:#6B7280;display:block;margin-bottom:4px">Moitiés</label>
                            <input type="number" name="moities_invendus" min="0" id="invM" value="{{ $moities }}" oninput="calcAttendu()" style="{{ $petit }}">
                        </div>
                        <div>
                            <label style="font-size:12px;color:#6B7280;display:block;margin-bottom:4px">Tiers</label>
                            <input type="number" name="tiers_invendus" min="0" id="invT" value="{{ $tiers }}" oninput="calcAttendu()" style="{{ $petit }}">
                        </div>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:14px;margin-top:12px">
                        <span style="font-weight:700;color:#111827">Montant attendu</span>
                        <span id="attendu" style="font-weight:700;color:#10B981">—</span>
                    </div>
                </div>
            @endif

            <div style="margin-bottom:18px">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Montant versé (FCFA) *</label>
                <input type="number" name="montant_verse" min="0" step="any" required
                       value="{{ old('montant_verse', (float) $versement->montant_verse == (int) $versement->montant_verse ? (int) $versement->montant_verse : $versement->montant_verse) }}"
                       style="{{ $champ }}">
            </div>

            <div style="margin-bottom:24px">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Date du versement *</label>
                <input type="date" name="date_versement" required max="{{ date('Y-m-d') }}"
                       value="{{ old('date_versement', $versement->date_versement->format('Y-m-d')) }}"
                       style="{{ $champ }}">
            </div>

            <button type="submit"
                    style="width:100%;background:#F97316;color:#FFFFFF;border:none;border-radius:14px;padding:16px 20px;font-size:16px;font-weight:700;cursor:pointer">
                Enregistrer la correction
            </button>
        </form>
    </div>

    @if($restantsModifiables)
        <script>
            function calcAttendu() {
                const parts   = {{ (int) $distribution->parts_confiees }};
                const prix    = {{ (int) ($distribution->prix_pain ?? 0) }};
                const inv     = (parseInt(document.getElementById('invE').value || 0) * 6)
                              + (parseInt(document.getElementById('invM').value || 0) * 3)
                              + (parseInt(document.getElementById('invT').value || 0) * 2);
                const attendu = Math.round(Math.max(0, parts - inv) * prix / 6);
                document.getElementById('attendu').textContent = attendu.toLocaleString('fr-FR') + ' FCFA';
            }
            calcAttendu();
        </script>
    @endif

@endsection
