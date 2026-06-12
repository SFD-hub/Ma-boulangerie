@extends('layouts.app')

@section('title', 'Vente du ' . $vente->date_vente->format('d/m/Y'))

@section('header')
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Vente du {{ $vente->date_vente->format('d/m/Y') }}</h1>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('ventes.index') }}"
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

    {{-- KPI --}}
    <div style="padding: 16px; background: #fff; border: 1px solid #0f766e; border-radius: 8px; margin-bottom: 24px; display: inline-block; min-width: 200px;">
        <p style="margin: 0 0 4px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Montant total</p>
        <p style="margin: 0; font-size: 26px; font-weight: 700; color: #0f766e;">{{ number_format($vente->montant_total, 0, ',', ' ') }} F</p>
    </div>

    {{-- Tableau détail --}}
    <h2 style="margin: 0 0 16px; color: #172033; font-size: 16px; font-weight: 700;">Détail des produits</h2>

    @if ($vente->details->isEmpty())
        <p style="color: #667085; text-align: center; padding: 40px; background: #f9f9f9; border-radius: 6px;">
            Aucun produit enregistré.
        </p>
    @else
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #d9dee7; background: #f9f9f9;">
                        <th style="padding: 12px; text-align: left; font-weight: 700; color: #172033;">Produit</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">Prix unit.</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">Quantité</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vente->details as $detail)
                        <tr style="border-bottom: 1px solid #d9dee7;">
                            <td style="padding: 12px; color: #172033;">{{ $detail->produit->nom }}</td>
                            <td style="padding: 12px; text-align: right; color: #667085;">{{ number_format($detail->prix_unitaire, 0, ',', ' ') }} F</td>
                            <td style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">{{ $detail->quantite }}</td>
                            <td style="padding: 12px; text-align: right; font-weight: 700; color: #0f766e;">{{ number_format($detail->quantite * $detail->prix_unitaire, 0, ',', ' ') }} F</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #f0f5f4; border-top: 2px solid #d9dee7;">
                        <td colspan="3" style="padding: 12px; font-weight: 700; color: #172033;">Total</td>
                        <td style="padding: 12px; text-align: right; font-weight: 700; color: #0f766e; font-size: 16px;">{{ number_format($vente->montant_total, 0, ',', ' ') }} F</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <div style="margin-top: 24px;">
        <form method="POST" action="{{ route('ventes.destroy', $vente) }}"
            onsubmit="return confirm('Supprimer cette vente ? Cette action est irréversible.');">
            @csrf
            @method('DELETE')
            <button type="submit"
                style="padding: 10px 16px; background: #fee; color: #c33; border: 1px solid #c33; border-radius: 6px; cursor: pointer; font-weight: 700;">
                Supprimer cette vente
            </button>
        </form>
    </div>
@endsection
