@extends('layouts.app')

@section('title', 'Ventes directes')

@section('header')
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Ventes directes</h1>
        <a href="{{ route('ventes.create') }}"
            style="padding: 10px 16px; background: #0f766e; color: white; border-radius: 6px; text-decoration: none; font-weight: 700;">
            + Nouvelle vente
        </a>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div style="background: #d4edda; border: 1px solid #28a745; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; color: #155724;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stat du mois --}}
    <div style="padding: 16px; background: #fff; border: 1px solid #0f766e; border-radius: 8px; margin-bottom: 24px; display: inline-block;">
        <p style="margin: 0 0 4px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Recettes ventes ce mois</p>
        <p style="margin: 0; font-size: 24px; font-weight: 700; color: #0f766e;">{{ number_format($totalMois, 0, ',', ' ') }} F</p>
    </div>

    @if ($ventes->isEmpty())
        <p style="color: #667085; text-align: center; padding: 40px; background: #f9f9f9; border-radius: 6px;">
            Aucune vente enregistrée.
        </p>
    @else
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #d9dee7; background: #f9f9f9;">
                        <th style="padding: 12px; text-align: left; font-weight: 700; color: #172033;">Date</th>
                        <th style="padding: 12px; text-align: left; font-weight: 700; color: #172033;">Produits</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">Montant total</th>
                        <th style="padding: 12px; text-align: center; font-weight: 700; color: #172033;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ventes as $vente)
                        <tr style="border-bottom: 1px solid #d9dee7;">
                            <td style="padding: 12px; color: #172033; font-weight: 700;">
                                {{ $vente->date_vente->format('d/m/Y') }}
                            </td>
                            <td style="padding: 12px; color: #667085; font-size: 13px;">
                                {{ $vente->details->map(fn($d) => $d->produit->nom . ' ×' . $d->quantite)->join(', ') }}
                            </td>
                            <td style="padding: 12px; text-align: right; font-weight: 700; color: #0f766e;">
                                {{ number_format($vente->montant_total, 0, ',', ' ') }} F
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <a href="{{ route('ventes.show', $vente) }}"
                                    style="padding: 6px 12px; background: #f0f5f4; color: #0f766e; border: 1px solid #0f766e; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 700; margin-right: 6px;">
                                    Voir
                                </a>
                                <form method="POST" action="{{ route('ventes.destroy', $vente) }}" style="display: inline;"
                                    onsubmit="return confirm('Supprimer cette vente ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        style="padding: 6px 12px; background: #fee; color: #c33; border: 1px solid #c33; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 700;">
                                        Suppr.
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px;">
            {{ $ventes->links() }}
        </div>
    @endif
@endsection
