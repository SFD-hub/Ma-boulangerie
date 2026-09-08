@extends('layouts.app')

@section('title', 'Historique factures')

@section('content')

    {{-- ── En-tête ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('clients-abonnes.show', $clientAbonne) }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Historique factures</span>
        <div style="width:36px"></div>
    </div>

    {{-- Sous-titre abonné --}}
    <div style="font-size:13px;color:#9CA3AF;margin-bottom:16px;text-align:center">
        {{ $clientAbonne->prenom }} {{ $clientAbonne->nom }}
    </div>

    @if(session('error'))
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:12px;padding:13px 16px;margin-bottom:16px;font-size:13px;color:#EF4444">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:12px;padding:14px 16px;margin-bottom:16px">
            @foreach($errors->all() as $msg)
                <div style="font-size:13px;color:#EF4444;padding:2px 0">{{ $msg }}</div>
            @endforeach
        </div>
    @endif

    @if($factures->isEmpty())

        <div style="text-align:center;padding:60px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">📄</div>
            Aucune facture générée.
        </div>

    @else

        <div style="background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">

            @foreach($factures as $facture)
                @php
                    $statut    = $facture->statut;
                    $estPayee  = $statut === 'payee';
                    $estPartielle = $statut === 'partielle';
                    $nomMois   = ucfirst(\Carbon\Carbon::create()->month($facture->mois)->locale('fr')->monthName);
                    $solde     = $facture->solde();
                    $montantPaye = $facture->montantPaye();
                    $badgeBg   = $estPayee ? '#ECFDF5' : ($estPartielle ? '#EFF6FF' : '#FFFBEB');
                    $badgeFg   = $estPayee ? '#059669' : ($estPartielle ? '#1D4ED8' : '#D97706');
                    $badgeLabel = $estPayee ? 'Payée' : ($estPartielle ? 'Partiellement payée' : 'Impayée');
                @endphp
                <div style="padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}{{ !$estPayee ? 'background:#FFFBEB;border-left:3px solid #F97316;' : '' }}">

                    {{-- Ligne 1 : période + badge statut --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:36px;height:36px;border-radius:9px;background:{{ $badgeBg }};display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">
                                {{ $estPayee ? '✅' : ($estPartielle ? '🕗' : '📄') }}
                            </div>
                            <div>
                                <div style="font-size:14px;font-weight:700;color:#111827">{{ $nomMois }} {{ $facture->annee }}</div>
                                <div style="font-size:12px;color:#9CA3AF;margin-top:1px">
                                    Générée le {{ $facture->date_facture->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                        <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:{{ $badgeBg }};color:{{ $badgeFg }};flex-shrink:0">
                            {{ $badgeLabel }}
                        </span>
                    </div>

                    {{-- Ligne 2 : détails --}}
                    <div style="font-size:13px;color:#6B7280;margin-bottom:4px;padding-left:44px">
                        {{ $facture->quantite_totale }}&nbsp;pains
                        @if($facture->prix_unitaire)
                            · {{ number_format($facture->prix_unitaire, 0, ',', ' ') }}&nbsp;FCFA/pain
                        @endif
                    </div>

                    {{-- Ligne 3 : montant + solde --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;padding-left:44px">
                        <span style="font-size:15px;font-weight:800;color:#111827">
                            {{ number_format($facture->montant_total, 0, ',', ' ') }}&nbsp;FCFA
                        </span>
                        @if($estPayee && $facture->date_paiement)
                            <span style="font-size:12px;color:#059669">
                                Payée le {{ $facture->date_paiement->format('d/m/Y') }}
                            </span>
                        @elseif($estPartielle)
                            <span style="font-size:12px;color:#1D4ED8">
                                Reste {{ number_format($solde, 0, ',', ' ') }}&nbsp;FCFA
                            </span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div style="display:flex;gap:8px;margin-top:10px;padding-left:44px;flex-wrap:wrap">

                        @if(!$estPayee)
                            <form method="POST" action="{{ route('factures.payer', $facture) }}"
                                  onsubmit="return confirm('Enregistrer le paiement du solde restant ({{ number_format($solde, 0, ',', ' ') }} FCFA) ?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        style="background:#059669;color:#FFFFFF;border:none;border-radius:10px;padding:8px 14px;font-size:13px;font-weight:600;cursor:pointer">
                                    ✓ Solder
                                </button>
                            </form>
                        @endif

                        {{-- PDF --}}
                        <a href="{{ route('factures.imprimer', $facture) }}" target="_blank"
                           style="display:inline-flex;align-items:center;gap:5px;background:#F3F4F6;color:#374151;border-radius:10px;padding:8px 14px;font-size:13px;font-weight:600;text-decoration:none">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            PDF
                        </a>

                        {{-- Supprimer --}}
                        <form method="POST" action="{{ route('factures.destroy', $facture) }}"
                              onsubmit="return confirm('Supprimer définitivement cette facture ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    style="background:none;border:none;color:#EF4444;font-size:13px;font-weight:600;cursor:pointer;padding:8px 4px">
                                Supprimer
                            </button>
                        </form>

                    </div>

                    {{-- Paiements par tranche --}}
                    <div style="padding-left:44px;margin-top:8px">
                        <details>
                            <summary style="cursor:pointer;list-style:none;font-size:12px;font-weight:600;color:#6B7280;user-select:none">
                                💰 Paiements ({{ $facture->paiementsFactures->count() }}){{ !$estPayee ? ' — payé ' . number_format($montantPaye, 0, ',', ' ') . ' / ' . number_format($facture->montant_total, 0, ',', ' ') . ' FCFA' : '' }}
                            </summary>

                            <div style="margin-top:10px;padding:12px;background:#F9FAFB;border-radius:12px">

                                @forelse($facture->paiementsFactures->sortByDesc('date_paiement') as $paiement)
                                    <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;{{ !$loop->last ? 'border-bottom:1px solid #E5E7EB;' : '' }}">
                                        <span style="font-size:13px;color:#374151">
                                            {{ $paiement->date_paiement->format('d/m/Y') }} — {{ number_format($paiement->montant, 0, ',', ' ') }}&nbsp;FCFA
                                        </span>
                                        <form method="POST" action="{{ route('paiements-factures.destroy', $paiement) }}"
                                              onsubmit="return confirm('Supprimer ce paiement ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" style="background:none;border:none;color:#EF4444;font-size:12px;cursor:pointer">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <div style="font-size:13px;color:#9CA3AF">Aucun paiement enregistré pour l'instant.</div>
                                @endforelse

                                @if(!$estPayee)
                                    <form method="POST" action="{{ route('factures.paiements.store', $facture) }}"
                                          style="display:flex;gap:8px;margin-top:10px;align-items:flex-end;flex-wrap:wrap">
                                        @csrf
                                        <div>
                                            <label style="display:block;font-size:11px;color:#6B7280;margin-bottom:3px">Montant (max {{ number_format($solde, 0, ',', ' ') }})</label>
                                            <input type="number" name="montant" step="0.01" min="0.01" max="{{ $solde }}" required
                                                   placeholder="ex: 3000"
                                                   style="width:130px;padding:8px 10px;border:1px solid #D1D5DB;border-radius:8px;font-size:13px">
                                        </div>
                                        <div>
                                            <label style="display:block;font-size:11px;color:#6B7280;margin-bottom:3px">Date</label>
                                            <input type="date" name="date_paiement" required value="{{ now()->toDateString() }}"
                                                   max="{{ now()->toDateString() }}"
                                                   style="padding:8px 10px;border:1px solid #D1D5DB;border-radius:8px;font-size:13px">
                                        </div>
                                        <button type="submit"
                                                style="background:#111827;color:#FFFFFF;border:none;border-radius:8px;padding:9px 14px;font-size:13px;font-weight:600;cursor:pointer">
                                            + Ajouter
                                        </button>
                                    </form>
                                @endif

                            </div>
                        </details>
                    </div>

                </div>
            @endforeach

        </div>

        {{-- ── Total ── --}}
        <div style="background:#F97316;border-radius:14px;padding:16px 20px;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:15px;font-weight:600;color:#FFFFFF">Total facturé</span>
            <span style="font-size:16px;font-weight:800;color:#FFFFFF">{{ number_format($factures->sum('montant_total'), 0, ',', ' ') }}&nbsp;FCFA</span>
        </div>

    @endif

@endsection
