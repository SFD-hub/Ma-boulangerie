@extends('layouts.app')

@section('title', 'Factures')

@section('header')
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Factures</h1>
        <a href="{{ route('factures.create') }}" style="padding: 10px 16px; background: #0f766e; color: white; border-radius: 6px; text-decoration: none; font-weight: 700;">
            + Ajouter une facture
        </a>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div style="background: #d4edda; border: 1px solid #28a745; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; color: #155724;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div style="background: #fee; border: 1px solid #c33; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; color: #c33;">
            {{ session('error') }}
        </div>
    @endif

    @if ($factures->isEmpty())
        <p style="color: #667085; text-align: center; padding: 40px;">
            Aucune facture trouvée. <a href="{{ route('factures.create') }}" style="color: #0f766e;">Créer une facture</a>
        </p>
    @else
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #d9dee7;">
                        <th style="padding: 12px; text-align: left; font-weight: 700; color: #172033;">Client</th>
                        <th style="padding: 12px; text-align: left; font-weight: 700; color: #172033;">Mois/Année</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">Montant</th>
                        <th style="padding: 12px; text-align: left; font-weight: 700; color: #172033;">Statut</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($factures as $facture)
                        <tr style="border-bottom: 1px solid #d9dee7;">
                            <td style="padding: 12px; color: #172033;">
                                <a href="{{ route('factures.show', $facture) }}" style="color: #0f766e; text-decoration: none;">
                                    {{ $facture->clientAbonne->nom }} {{ $facture->clientAbonne->prenom }}
                                </a>
                            </td>
                            <td style="padding: 12px; color: #667085;">
                                {{ $facture->mois }}/{{ $facture->annee }}
                            </td>
                            <td style="padding: 12px; text-align: right; color: #172033;">
                                {{ number_format($facture->montant_total, 2, ',', ' ') }} €
                            </td>
                            <td style="padding: 12px; color: #667085;">
                                {{ ucfirst($facture->statut) }}
                            </td>
                            <td style="padding: 12px; text-align: right;">
                                <a 
                                    href="{{ route('factures.show', $facture) }}"
                                    style="color: #0f766e; text-decoration: none; margin-right: 12px;"
                                >
                                    Voir
                                </a>
                                <a 
                                    href="{{ route('factures.edit', $facture) }}"
                                    style="color: #0f766e; text-decoration: none; margin-right: 12px;"
                                >
                                    Éditer
                                </a>
                                <form 
                                    method="POST" 
                                    action="{{ route('factures.destroy', $facture) }}"
                                    style="display: inline;"
                                    onsubmit="return confirm('Êtes-vous sûr ?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="submit"
                                        style="background: none; border: none; color: #c33; cursor: pointer; text-decoration: none;"
                                    >
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
