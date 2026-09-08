@extends('layouts.app')

@section('title', 'Distribution du ' . $distribution->date_distribution->format('d/m/Y'))

@section('header')
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Distribution du {{ $distribution->date_distribution->format('d/m/Y') }}</h1>
        <div style="display: flex; gap: 12px;">
            @if(!$distribution->estRegle())
                <a href="{{ route('distributions.edit', $distribution) }}"
                    style="padding: 10px 16px; background: #0f766e; color: white; border-radius: 6px; text-decoration: none; font-weight: 700;">
                    Éditer
                </a>
            @endif
            <a href="{{ route('livreurs.distributions', $distribution->livreur) }}"
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

    @if (session('error'))
        <div style="background: #fee; border: 1px solid #c33; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; color: #c33;">
            {{ session('error') }}
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
            <p style="margin: 0 0 6px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Produit</p>
            <p style="margin: 0; font-size: 15px; font-weight: 700; color: #172033;">{{ $distribution->produit->nom ?? '—' }}</p>
        </div>
        <div style="padding: 16px; background: #fff; border: 1px solid #d9dee7; border-radius: 8px;">
            <p style="margin: 0 0 6px; color: #667085; font-size: 12px; text-transform: uppercase; font-weight: 700;">Pains attribués</p>
            <p style="margin: 0; font-size: 20px; font-weight: 700; color: #172033;">{{ $distribution->pains_attribues }}</p>
            <p style="margin: 4px 0 0; font-size: 11px; color: #667085;">{{ number_format($distribution->prix_pain, 0, ',', ' ') }} F / pain</p>
        </div>
        @if ($distribution->versement)
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

    <div style="padding: 16px; background: #f9f9f9; border-radius: 8px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; padding: 6px 0;">
            <span style="color: #667085; font-weight: 600;">Montant attendu</span>
            <span style="font-weight: 700; color: #172033;">{{ number_format($distribution->montant_attendu, 0, ',', ' ') }} F</span>
        </div>
        @if ($distribution->versement)
            <div style="display: flex; justify-content: space-between; padding: 6px 0; border-top: 1px solid #e5e7eb;">
                <span style="color: #667085; font-weight: 600;">Montant versé</span>
                <span style="font-weight: 700; color: #059669;">{{ number_format($distribution->versement->montant_verse, 0, ',', ' ') }} F</span>
            </div>
        @else
            <div style="padding: 6px 0; border-top: 1px solid #e5e7eb; font-style: italic; color: #9ca3af; font-size: 13px;">
                Versement non enregistré
            </div>
        @endif
    </div>

    {{-- Actions --}}
    <div style="background: #f0f5f4; padding: 16px; border-radius: 6px; border: 1px solid #d9dee7; display: flex; gap: 12px;">
        @if(!$distribution->estRegle())
            <a href="{{ route('distributions.edit', $distribution) }}"
                style="padding: 10px 16px; background: #0f766e; color: white; text-decoration: none; border-radius: 6px; font-weight: 700;">
                ✎ Éditer
            </a>
        @endif
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
