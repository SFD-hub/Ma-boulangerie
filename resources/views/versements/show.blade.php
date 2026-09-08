@extends('layouts.app')

@section('title', 'Versement du ' . $versement->date_versement->format('d/m/Y'))

@section('header')
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Versement du {{ $versement->date_versement->format('d/m/Y') }}</h1>
        <div style="display: flex; gap: 12px;">
            <a 
                href="{{ route('versements.edit', $versement) }}"
                style="padding: 10px 16px; background: #0f766e; color: white; border-radius: 6px; text-decoration: none; font-weight: 700;"
            >
                Éditer
            </a>
            <a 
                href="{{ route('versements.index') }}"
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

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
        <div>
            <div style="margin-bottom: 24px;">
                <p style="margin: 0 0 8px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Date</p>
                <p style="margin: 0; color: #172033; font-size: 16px; font-weight: 700;">
                    {{ $versement->date_versement->format('d/m/Y') }}
                </p>
            </div>

            <div style="margin-bottom: 24px;">
                <p style="margin: 0 0 8px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Livreur</p>
                <p style="margin: 0; color: #172033; font-size: 16px;">
                    <a href="{{ route('livreurs.show', $versement->livreur) }}" style="color: #0f766e; text-decoration: none;">
                        {{ $versement->livreur->prenom }} {{ $versement->livreur->nom }}
                    </a>
                </p>
            </div>

            <div style="margin-bottom: 24px;">
                <p style="margin: 0 0 8px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Montant versé</p>
                <p style="margin: 0; color: #172033; font-size: 20px; font-weight: 700;">{{ number_format($versement->montant_verse, 0, ',', ' ') }} FCFA</p>
            </div>
        </div>

        <div>
            <div style="background: #f0f5f4; padding: 16px; border-radius: 6px; border: 1px solid #d9dee7;">
                <h3 style="margin: 0 0 16px; color: #172033; font-size: 14px; font-weight: 700; text-transform: uppercase;">Actions</h3>
                
                <a 
                    href="{{ route('versements.edit', $versement) }}"
                    style="display: block; padding: 10px 12px; margin-bottom: 8px; background: #0f766e; color: white; text-decoration: none; border-radius: 4px; text-align: center; font-weight: 700;"
                >
                    ✎ Éditer
                </a>

                <form 
                    method="POST" 
                    action="{{ route('versements.destroy', $versement) }}"
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

            <div style="margin-top: 16px; background: #f9f9f9; padding: 12px; border-radius: 6px; border: 1px solid #d9dee7; font-size: 12px; color: #667085;">
                <p style="margin: 0 0 4px;"><strong>Créé :</strong> {{ $versement->created_at->format('d/m/Y H:i') }}</p>
                <p style="margin: 0;"><strong>Modifié :</strong> {{ $versement->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
@endsection
