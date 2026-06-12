@extends('layouts.app')

@section('title', 'Distributions')

@section('header')
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Distributions</h1>
        <a href="{{ route('distributions.create') }}" style="padding: 10px 16px; background: #0f766e; color: white; border-radius: 6px; text-decoration: none; font-weight: 700;">
            + Ajouter une distribution
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

    @if ($distributions->isEmpty())
        <p style="color: #667085; text-align: center; padding: 40px;">
            Aucune distribution trouvée. <a href="{{ route('distributions.create') }}" style="color: #0f766e;">Créer une distribution</a>
        </p>
    @else
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #d9dee7;">
                        <th style="padding: 12px; text-align: left; font-weight: 700; color: #172033;">Date</th>
                        <th style="padding: 12px; text-align: left; font-weight: 700; color: #172033;">Livreur</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">Montant attendu</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">Produits</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($distributions as $distribution)
                        <tr style="border-bottom: 1px solid #d9dee7;">
                            <td style="padding: 12px; color: #172033; font-weight: 700;">
                                {{ $distribution->date_distribution->format('d/m/Y') }}
                            </td>
                            <td style="padding: 12px; color: #172033;">
                                <a href="{{ route('livreurs.show', $distribution->livreur) }}" style="color: #0f766e; text-decoration: none;">
                                    {{ $distribution->livreur->prenom }} {{ $distribution->livreur->nom }}
                                </a>
                            </td>
                            <td style="padding: 12px; text-align: right; color: #172033; font-weight: 700;">
                                {{ number_format($distribution->montant_attendu, 2, ',', ' ') }} €
                            </td>
                            <td style="padding: 12px; text-align: right; color: #667085;">
                                {{ $distribution->detailDistributions->count() }} article{{ $distribution->detailDistributions->count() !== 1 ? 's' : '' }}
                            </td>
                            <td style="padding: 12px; text-align: right;">
                                <a 
                                    href="{{ route('distributions.show', $distribution) }}"
                                    style="color: #0f766e; text-decoration: none; margin-right: 12px;"
                                >
                                    Voir
                                </a>
                                <a 
                                    href="{{ route('distributions.edit', $distribution) }}"
                                    style="color: #0f766e; text-decoration: none; margin-right: 12px;"
                                >
                                    Éditer
                                </a>
                                <form 
                                    method="POST" 
                                    action="{{ route('distributions.destroy', $distribution) }}"
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
