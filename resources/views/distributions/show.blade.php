@extends('layouts.app')

@section('title', 'Distribution du ' . $distribution->date_distribution->format('d/m/Y'))

@section('header')
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Distribution du {{ $distribution->date_distribution->format('d/m/Y') }}</h1>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('distributions.edit', $distribution) }}"
                style="padding: 10px 16px; background: #0f766e; color: white; border-radius: 6px; text-decoration: none; font-weight: 700;">
                Éditer
            </a>
            <a href="{{ route('distributions.index') }}"
                style="padding: 10px 16px; background: #f0f0f0; color: #172033; border: 1px solid #d9dee7; border-radius: 6px; text-decoration: none; font-weight: 700;">
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

    {{-- Métriques clés --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="padding: 16px; background: #fff; border: 1px solid #d9dee7; border-radius: 8px;">
            <p style="margin: 0 0 6px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Livreur</p>
            <p style="margin: 0; font-size: 15px; font-weight: 700; color: #172033;">
                <a href="{{ route('livreurs.show', $distribution->livreur) }}" style="color: #0f766e; text-decoration: none;">
                    {{ $distribution->livreur->prenom }} {{ $distribution->livreur->nom }}
                </a>
            </p>
        </div>
        <div style="padding: 16px; background: #fff; border: 1px solid #d9dee7; border-radius: 8px;">
            <p style="margin: 0 0 6px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Montant attendu</p>
            <p style="margin: 0; font-size: 20px; font-weight: 700; color: #172033;">{{ number_format($distribution->montant_attendu, 0, ',', ' ') }} F</p>
        </div>
        @if ($distribution->detailDistributions->isNotEmpty())
            <div style="padding: 16px; background: #fff; border: 1px solid #28a745; border-radius: 8px;">
                <p style="margin: 0 0 6px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Pains vendus</p>
                <p style="margin: 0; font-size: 20px; font-weight: 700; color: #155724;">{{ $distribution->pains_vendus }}</p>
            </div>
            <div style="padding: 16px; background: #fff; border: 1px solid #f59e0b; border-radius: 8px;">
                <p style="margin: 0 0 6px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Invendus (retournés)</p>
                <p style="margin: 0; font-size: 20px; font-weight: 700; color: #92400e;">{{ $distribution->invendus }}</p>
            </div>
        @endif
        <div style="padding: 16px; background: #fff; border: 1px solid {{ $reliquat > 0 ? '#c33' : '#28a745' }}; border-radius: 8px;">
            <p style="margin: 0 0 6px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Reliquat livreur (total)</p>
            <p style="margin: 0; font-size: 20px; font-weight: 700; color: {{ $reliquat > 0 ? '#c33' : '#155724' }};">
                {{ number_format($reliquat, 0, ',', ' ') }} F
            </p>
            <p style="margin: 4px 0 0; font-size: 11px; color: #667085;">Versé : {{ number_format($totalVerse, 0, ',', ' ') }} F</p>
        </div>
    </div>

    {{-- Tableau des produits --}}
    <h2 style="margin: 0 0 16px; color: #172033; font-size: 16px; font-weight: 700;">Détail des produits</h2>

    @if ($distribution->detailDistributions->isEmpty())
        <p style="color: #667085; text-align: center; padding: 40px; background: #f9f9f9; border-radius: 6px;">
            Aucun produit pour cette distribution.
        </p>
    @else
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #d9dee7; background: #f9f9f9;">
                        <th style="padding: 12px; text-align: left; font-weight: 700; color: #172033;">Produit</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">Prix unit.</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">Attribués</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #28a745;">Vendus</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #f59e0b;">Retournés</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($distribution->detailDistributions as $detail)
                        @php
                            $vendus  = max(0, $detail->quantite_attribuee - $detail->quantite_retournee);
                            $montant = $vendus * $detail->produit->prix;
                        @endphp
                        <tr style="border-bottom: 1px solid #d9dee7;">
                            <td style="padding: 12px; color: #172033;">
                                <a href="{{ route('produits.show', $detail->produit) }}" style="color: #0f766e; text-decoration: none;">
                                    {{ $detail->produit->nom }}
                                </a>
                            </td>
                            <td style="padding: 12px; text-align: right; color: #667085;">{{ number_format($detail->produit->prix, 0, ',', ' ') }} F</td>
                            <td style="padding: 12px; text-align: right; color: #172033; font-weight: 700;">{{ $detail->quantite_attribuee }}</td>
                            <td style="padding: 12px; text-align: right; color: #155724; font-weight: 700;">{{ $vendus }}</td>
                            <td style="padding: 12px; text-align: right; color: #92400e; font-weight: 700;">{{ $detail->quantite_retournee }}</td>
                            <td style="padding: 12px; text-align: right; color: #172033; font-weight: 700;">{{ number_format($montant, 0, ',', ' ') }} F</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #f0f5f4; border-top: 2px solid #d9dee7;">
                        <td colspan="3" style="padding: 12px; font-weight: 700; color: #172033;">Total</td>
                        <td style="padding: 12px; text-align: right; font-weight: 700; color: #155724;">{{ $distribution->pains_vendus }}</td>
                        <td style="padding: 12px; text-align: right; font-weight: 700; color: #92400e;">{{ $distribution->invendus }}</td>
                        <td style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">{{ number_format($distribution->montant_attendu, 0, ',', ' ') }} F</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    {{-- Actions --}}
    <div style="margin-top: 24px; background: #f0f5f4; padding: 16px; border-radius: 6px; border: 1px solid #d9dee7; display: flex; gap: 12px;">
        <a href="{{ route('distributions.edit', $distribution) }}"
            style="padding: 10px 16px; background: #0f766e; color: white; text-decoration: none; border-radius: 6px; font-weight: 700;">
            ✎ Éditer
        </a>
        <form method="POST" action="{{ route('distributions.destroy', $distribution) }}"
            onsubmit="return confirm('Êtes-vous sûr ? Cette action est irréversible.');">
            @csrf
            @method('DELETE')
            <button type="submit"
                style="padding: 10px 16px; background: #fee; color: #c33; border: 1px solid #c33; border-radius: 6px; cursor: pointer; font-weight: 700;">
                🗑 Supprimer
            </button>
        </form>
    </div>
@endsection
