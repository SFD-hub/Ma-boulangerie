@extends('layouts.app')

@section('title', 'Facture')

@section('content')

    <a href="{{ route('clients-abonnes.show', $facture->clientAbonne) }}" class="back-link">← {{ $facture->clientAbonne->prenom }} {{ $facture->clientAbonne->nom }}</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <div class="card-title" style="margin:0">Facture {{ $facture->mois }}/{{ $facture->annee }}</div>
            <span class="badge {{ $facture->statut === 'payee' ? 'badge-success' : 'badge-warning' }}">
                {{ $facture->statut === 'payee' ? 'Payée' : 'Impayée' }}
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-row-label">Client</span>
            <span class="detail-row-value">{{ $facture->clientAbonne->prenom }} {{ $facture->clientAbonne->nom }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Période</span>
            <span class="detail-row-value">{{ $facture->mois }}/{{ $facture->annee }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Quantité totale</span>
            <span class="detail-row-value">{{ $facture->quantite_totale }} pains</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Montant total</span>
            <span class="detail-row-value" style="font-weight:700;font-size:18px;color:var(--brand)">
                {{ number_format($facture->montant_total, 0, ',', ' ') }} FCFA
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Date facture</span>
            <span class="detail-row-value">{{ $facture->date_facture->format('d/m/Y') }}</span>
        </div>
    </div>

    <div style="display:flex;gap:8px;margin-top:8px">
        <a href="{{ route('factures.edit', $facture) }}" class="btn btn-secondary" style="flex:1;text-align:center">
            Modifier
        </a>
        <form method="POST" action="{{ route('factures.destroy', $facture) }}" style="flex:1"
              onsubmit="return confirm('Supprimer cette facture ?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn" style="width:100%;background:#fff;border:1px solid #e53e3e;color:#e53e3e">
                Supprimer
            </button>
        </form>
    </div>

@endsection
