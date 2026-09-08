@extends('layouts.app')

@section('title', 'Versements')

@section('header')
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Versements</h1>
        <a href="{{ route('versements.create') }}" style="padding: 10px 16px; background: #0f766e; color: white; border-radius: 6px; text-decoration: none; font-weight: 700;">
            + Ajouter un versement
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

    @if ($versements->isEmpty())
        <p style="color: #667085; text-align: center; padding: 40px;">
            Aucun versement trouvé. <a href="{{ route('versements.create') }}" style="color: #0f766e;">Créer un versement</a>
        </p>
    @else
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #d9dee7;">
                        <th style="padding: 12px; text-align: left; font-weight: 700; color: #172033;">Date</th>
                        <th style="padding: 12px; text-align: left; font-weight: 700; color: #172033;">Livreur</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">Montant versé</th>
                        <th style="padding: 12px; text-align: right; font-weight: 700; color: #172033;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($versements as $versement)
                        <tr style="border-bottom: 1px solid #d9dee7;">
                            <td style="padding: 12px; color: #172033; font-weight: 700;">
                                {{ $versement->date_versement->format('d/m/Y') }}
                            </td>
                            <td style="padding: 12px; color: #172033;">
                                <a href="{{ route('livreurs.show', $versement->livreur) }}" style="color: #0f766e; text-decoration: none;">
                                    {{ $versement->livreur->prenom }} {{ $versement->livreur->nom }}
                                </a>
                            </td>
                            <td style="padding: 12px; text-align: right; color: #172033; font-weight: 700;">
                                {{ number_format($versement->montant_verse, 0, ',', ' ') }} FCFA
                            </td>
                            <td style="padding: 12px; text-align: right;">
                                <a 
                                    href="{{ route('versements.show', $versement) }}"
                                    style="color: #0f766e; text-decoration: none; margin-right: 12px;"
                                >
                                    Voir
                                </a>
                                <a 
                                    href="{{ route('versements.edit', $versement) }}"
                                    style="color: #0f766e; text-decoration: none; margin-right: 12px;"
                                >
                                    Éditer
                                </a>
                                <form 
                                    method="POST" 
                                    action="{{ route('versements.destroy', $versement) }}"
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

        {{-- ── Pagination ── --}}
        @if($versements->hasPages())
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0 8px">

                @if($versements->onFirstPage())
                    <span style="font-size:14px;font-weight:600;color:#D1D5DB;padding:10px 16px;background:#F9FAFB;border-radius:10px">
                        ← Précédent
                    </span>
                @else
                    <a href="{{ $versements->previousPageUrl() }}"
                       style="font-size:14px;font-weight:600;color:#0f766e;padding:10px 16px;background:#EFFAF8;border-radius:10px;text-decoration:none">
                        ← Précédent
                    </a>
                @endif

                <span style="font-size:13px;color:#9CA3AF;font-weight:500">
                    Page {{ $versements->currentPage() }} / {{ $versements->lastPage() }}
                </span>

                @if($versements->hasMorePages())
                    <a href="{{ $versements->nextPageUrl() }}"
                       style="font-size:14px;font-weight:600;color:#0f766e;padding:10px 16px;background:#EFFAF8;border-radius:10px;text-decoration:none">
                        Suivant →
                    </a>
                @else
                    <span style="font-size:14px;font-weight:600;color:#D1D5DB;padding:10px 16px;background:#F9FAFB;border-radius:10px">
                        Suivant →
                    </span>
                @endif

            </div>
        @endif
    @endif
@endsection
