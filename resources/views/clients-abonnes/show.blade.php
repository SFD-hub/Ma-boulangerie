@extends('layouts.app')

@section('title', $clientAbonne->prenom . ' ' . $clientAbonne->nom)

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('clients-abonnes.index') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Détail abonné</span>
        <div style="width:36px"></div>
    </div>

    {{-- ── Profil (conservé tel quel) ── --}}
    <div class="card" style="text-align:center;padding:24px 16px 20px">
        <div class="avatar" style="width:60px;height:60px;font-size:24px;margin:0 auto 10px">
            {{ strtoupper(substr($clientAbonne->prenom, 0, 1)) }}
        </div>
        <div style="font-size:18px;font-weight:700">{{ $clientAbonne->prenom }} {{ $clientAbonne->nom }}</div>
        <div style="font-size:14px;color:var(--text2);margin-top:2px">{{ $clientAbonne->telephone }}</div>
        @if($clientAbonne->adresse)
            <div style="font-size:13px;color:var(--text2);margin-top:2px">{{ $clientAbonne->adresse }}</div>
        @endif
        <div style="margin-top:10px;display:flex;justify-content:center;align-items:center;gap:12px">
            <span class="badge {{ $clientAbonne->actif ? 'badge-green' : 'badge-gray' }}">
                {{ $clientAbonne->actif ? 'Actif' : 'Inactif' }}
            </span>
            <a href="{{ route('clients-abonnes.edit', $clientAbonne) }}" style="font-size:13px;color:var(--orange)">
                Modifier
            </a>
        </div>
    </div>

    {{-- ── Historique consommations (aperçu) ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
        <span style="font-size:16px;font-weight:700;color:#111827">Historique consommations</span>
        <a href="{{ route('clients-abonnes.consommations.historique', $clientAbonne) }}"
           style="font-size:13px;font-weight:600;color:#F97316;text-decoration:none">Voir plus</a>
    </div>

    @php $aperçuConso = $clientAbonne->consommations()->orderByDesc('date_consommation')->limit(3)->get(); @endphp

    @if($aperçuConso->isEmpty())
        <div style="text-align:center;padding:20px 0 16px;color:#9CA3AF;font-size:14px">
            Aucune consommation enregistrée.
        </div>
    @else
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">
            @foreach($aperçuConso as $conso)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB' }}">
                    <span style="font-size:14px;font-weight:600;color:#111827">
                        {{ $conso->date_consommation->format('d/m/Y') }}
                    </span>
                    <span style="font-size:14px;font-weight:700;color:#F97316">
                        {{ $conso->quantite }}&nbsp;pains
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Boutons d'action ── --}}
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px">
        <a href="{{ route('clients-abonnes.consommation.form', $clientAbonne) }}"
           style="display:flex;align-items:center;justify-content:center;gap:8px;background:transparent;color:#F97316;border:2px solid #F97316;border-radius:14px;padding:15px 20px;font-size:16px;font-weight:700;text-decoration:none;box-sizing:border-box">
            + Consommation
        </a>
        <a href="{{ route('clients-abonnes.facture.form', $clientAbonne) }}"
           style="display:flex;align-items:center;justify-content:center;gap:8px;background:#F97316;color:#FFFFFF;border-radius:14px;padding:15px 20px;font-size:16px;font-weight:700;text-decoration:none">
            📄 Générer facture
        </a>
    </div>

    {{-- ── Factures récentes ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
        <span style="font-size:16px;font-weight:700;color:#111827">Factures</span>
        <a href="{{ route('clients-abonnes.factures.historique', $clientAbonne) }}"
           style="font-size:13px;font-weight:600;color:#F97316;text-decoration:none">Voir plus</a>
    </div>

    @php $aperçuFactures = $clientAbonne->factures()->orderByDesc('annee')->orderByDesc('mois')->limit(3)->get(); @endphp

    @if($aperçuFactures->isEmpty())
        <div style="text-align:center;padding:20px 0 16px;color:#9CA3AF;font-size:14px">
            Aucune facture générée.
        </div>
    @else
        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">
            @foreach($aperçuFactures as $facture)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB' }}">
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#111827">
                            {{ ucfirst(\Carbon\Carbon::create()->month($facture->mois)->locale('fr')->monthName) }}
                            {{ $facture->annee }}
                        </div>
                        <div style="font-size:12px;color:#6B7280;margin-top:2px">
                            {{ $facture->quantite_totale }}&nbsp;pains
                        </div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:14px;font-weight:700;color:#111827">
                            {{ number_format($facture->montant_total, 0, ',', ' ') }}&nbsp;FCFA
                        </div>
                        <span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px;display:inline-block;margin-top:3px;background:{{ $facture->statut === 'payee' ? '#ECFDF5' : '#FFFBEB' }};color:{{ $facture->statut === 'payee' ? '#059669' : '#D97706' }}">
                            {{ $facture->statut === 'payee' ? 'Payée' : 'Impayée' }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection
