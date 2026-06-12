@extends('layouts.app')

@section('title', 'Paiement Facture')

@section('header')
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Paiement Facture</h1>
        <div style="display: flex; gap: 12px;">
            <a 
                href="{{ route('paiements-factures.edit', $paiementFacture) }}"
                style="padding: 10px 16px; background: #0f766e; color: white; border-radius: 6px; text-decoration: none; font-weight: 700;"
            >
                Éditer
            </a>
            <a 
                href="{{ route('paiements-factures.index') }}"
                style="padding: 10px 16px; background: #f0f0f0; color: #172033; border: 1px solid #d9dee7; border-radius: 6px; text-decoration: none; font-weight: 700;"
            >
                Retour
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div style="background: #d4edda; border: 1px solid #28a745; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; color: #155724;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <div>
            <div style="margin-bottom: 24px;">
                <p style="margin: 0 0 8px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Facture</p>
                <p style="margin: 0; color: #172033; font-size: 16px;">{{ $paiementFacture->facture->mois }}/{{ $paiementFacture->facture->annee }}</p>
            </div>

            <div style="margin-bottom: 24px;">
                <p style="margin: 0 0 8px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Client</p>
                <p style="margin: 0; color: #172033; font-size: 16px;">{{ $paiementFacture->facture->clientAbonne->nom }} {{ $paiementFacture->facture->clientAbonne->prenom }}</p>
            </div>

            <div style="margin-bottom: 24px;">
                <p style="margin: 0 0 8px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Montant</p>
                <p style="margin: 0; color: #172033; font-size: 16px; font-weight: 700;">{{ number_format($paiementFacture->montant, 2, ',', ' ') }} €</p>
            </div>

            <div style="margin-bottom: 24px;">
                <p style="margin: 0 0 8px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Date de paiement</p>
                <p style="margin: 0; color: #172033; font-size: 16px;">{{ $paiementFacture->date_paiement->format('d/m/Y') }}</p>
            </div>

            <div style="margin-bottom: 24px;">
                <p style="margin: 0 0 8px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Créé le</p>
                <p style="margin: 0; color: #172033; font-size: 14px;">{{ $paiementFacture->created_at->format('d/m/Y à H:i') }}</p>
            </div>

            @if ($paiementFacture->updated_at->ne($paiementFacture->created_at))
                <div style="margin-bottom: 24px;">
                    <p style="margin: 0 0 8px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Mis à jour le</p>
                    <p style="margin: 0; color: #172033; font-size: 14px;">{{ $paiementFacture->updated_at->format('d/m/Y à H:i') }}</p>
                </div>
            @endif
        </div>

        <div>
            <div style="background: #f9f9f9; padding: 16px; border-radius: 6px; border: 1px solid #d9dee7;">
                <h3 style="margin: 0 0 16px; color: #172033; font-size: 14px; font-weight: 700; text-transform: uppercase;">Actions</h3>
                
                <a 
                    href="{{ route('paiements-factures.edit', $paiementFacture) }}"
                    style="display: block; padding: 10px 12px; margin-bottom: 8px; background: #0f766e; color: white; text-decoration: none; border-radius: 4px; text-align: center; font-weight: 700;"
                >
                    ✎ Éditer
                </a>

                <form 
                    method="POST" 
                    action="{{ route('paiements-factures.destroy', $paiementFacture) }}"
                    onsubmit="return confirm('Êtes-vous sûr ? Cette action est irréversible.');"
                >
                    @csrf
                    @method('DELETE')
                    <button 
                        type="submit"
                        style="display: block; width: 100%; padding: 10px 12px; background: #fee; color: #c33; border: 1px solid #c33; border-radius: 4px; cursor: pointer; font-weight: 700;"
                    >
                        🗑 Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
