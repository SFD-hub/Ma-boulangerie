@extends('layouts.app')

@section('title', 'Générer facture')

@section('content')

    {{-- ── En-tête ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('clients-abonnes.show', $clientAbonne) }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Générer facture</span>
        <div style="width:36px"></div>
    </div>

    {{-- ── Erreurs ── --}}
    @if($errors->any())
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:12px;padding:14px 16px;margin-bottom:16px">
            @foreach($errors->all() as $msg)
                <div style="font-size:13px;color:#EF4444;padding:2px 0">{{ $msg }}</div>
            @endforeach
        </div>
    @endif

    @if(session('error'))
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:12px;padding:14px 16px;margin-bottom:16px;font-size:13px;color:#EF4444">
            {{ session('error') }}
        </div>
    @endif

    <div style="background:#FFFFFF;border-radius:18px;padding:20px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07)">

        <form method="POST" action="{{ route('clients-abonnes.factures.store', $clientAbonne) }}">
            @csrf

            {{-- Abonné (affichage) --}}
            <div style="margin-bottom:18px">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Abonné</label>
                <div style="background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:13px 16px;font-size:15px;font-weight:600;color:#111827;display:flex;align-items:center;gap:10px">
                    <div style="width:32px;height:32px;border-radius:8px;background:#F97316;color:#FFFFFF;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0">
                        {{ strtoupper(substr($clientAbonne->prenom, 0, 1)) }}
                    </div>
                    {{ $clientAbonne->prenom }} {{ $clientAbonne->nom }}
                </div>
            </div>

            {{-- Mois + Année --}}
            <div style="display:flex;gap:12px;margin-bottom:18px">
                <div style="flex:2">
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Mois *</label>
                    <div style="position:relative">
                        <select name="mois" id="selectMois" required onchange="updatePreview()"
                                style="width:100%;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;font-size:15px;color:#111827;box-sizing:border-box;outline:none;appearance:none;-webkit-appearance:none">
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ old('mois', now()->month) == $m ? 'selected' : '' }}>
                                    {{ ucfirst(\Carbon\Carbon::create()->month($m)->locale('fr')->monthName) }}
                                </option>
                            @endforeach
                        </select>
                        <div style="position:absolute;right:12px;top:50%;transform:translateY(-50%);pointer-events:none;color:#9CA3AF">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div style="flex:1">
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Année *</label>
                    <input type="number" name="annee" id="inputAnnee" min="2020"
                           value="{{ old('annee', now()->year) }}" required
                           oninput="updatePreview()"
                           style="width:100%;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;font-size:15px;color:#111827;box-sizing:border-box;outline:none">
                </div>
            </div>

            {{-- Prix du pain --}}
            <div style="margin-bottom:18px">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Prix du pain (FCFA) *</label>
                <input type="number" name="prix_unitaire" id="inputPrix" min="1"
                       value="{{ old('prix_unitaire', $prixPain) }}"
                       placeholder="Ex: 100" required
                       oninput="updatePreview()"
                       style="width:100%;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;font-size:15px;color:#111827;box-sizing:border-box;outline:none">
            </div>

            {{-- Prévisualisation automatique --}}
            <div id="facturePreview" style="display:none;border-radius:12px;padding:14px 16px;margin-bottom:18px">
                <div style="font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px">
                    Prévisualisation de la facture
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0">
                    <span style="color:#6B7280">Total consommé</span>
                    <span id="prevTotal" style="font-weight:700;color:#111827">—</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0">
                    <span style="color:#6B7280">Prix du pain</span>
                    <span id="prevPrix" style="font-weight:700;color:#111827">—</span>
                </div>
                <div id="prevDividerLine" style="border-top:1px solid #BBF7D0;margin:8px 0"></div>
                <div style="display:flex;justify-content:space-between;font-size:14px;padding:4px 0">
                    <span style="font-weight:700;color:#111827">Montant facture</span>
                    <span id="prevMontant" style="font-weight:800;font-size:15px">—</span>
                </div>
                <div id="prevAvertissement" style="display:none;margin-top:8px;font-size:12px;font-weight:600;color:#D97706">
                    ⚠️ Aucune consommation enregistrée pour ce mois.
                </div>
            </div>

            <button type="submit" id="btnGenerer"
                    style="width:100%;background:#F97316;color:#FFFFFF;border:none;border-radius:14px;padding:16px 20px;font-size:16px;font-weight:700;cursor:pointer">
                Générer la facture
            </button>

        </form>
    </div>

    <script>
        const _consosData = @json($consommationsData);

        function updatePreview() {
            const mois  = parseInt(document.getElementById('selectMois').value || 0);
            const annee = parseInt(document.getElementById('inputAnnee').value || 0);
            const prix  = parseInt(document.getElementById('inputPrix').value || 0);

            if (!mois || !annee) return;

            const key   = annee + '-' + String(mois).padStart(2, '0');
            const total = _consosData[key] || 0;
            const montant = total * (prix > 0 ? prix : 0);

            const preview = document.getElementById('facturePreview');
            const avert   = document.getElementById('prevAvertissement');
            const divider = document.getElementById('prevDividerLine');

            document.getElementById('prevTotal').textContent = total + ' pains';
            document.getElementById('prevPrix').textContent  = prix > 0 ? prix.toLocaleString('fr-FR') + ' FCFA' : '—';

            if (total === 0) {
                preview.style.cssText  = 'display:block;background:#FFFBEB;border:1px solid #FDE68A;border-radius:12px;padding:14px 16px;margin-bottom:18px';
                divider.style.borderColor = '#FDE68A';
                document.getElementById('prevMontant').textContent  = '0 FCFA';
                document.getElementById('prevMontant').style.color  = '#D97706';
                avert.style.display = 'block';
            } else if (prix > 0) {
                preview.style.cssText  = 'display:block;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:14px 16px;margin-bottom:18px';
                divider.style.borderColor = '#BBF7D0';
                document.getElementById('prevMontant').textContent  = montant.toLocaleString('fr-FR') + ' FCFA';
                document.getElementById('prevMontant').style.color  = '#059669';
                avert.style.display = 'none';
            } else {
                preview.style.cssText  = 'display:block;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;margin-bottom:18px';
                document.getElementById('prevMontant').textContent  = '—';
                document.getElementById('prevMontant').style.color  = '#9CA3AF';
                avert.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', updatePreview);
    </script>

@endsection
