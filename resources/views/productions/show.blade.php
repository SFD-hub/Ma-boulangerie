@extends('layouts.app')

@section('title', 'Production')

@section('content')

    <a href="{{ route('productions.index') }}" class="back-link">← Productions</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <div class="card-title" style="margin:0">Production du {{ $production->date_production->format('d/m/Y') }}</div>
            <a href="{{ route('productions.edit', $production) }}" class="btn btn-secondary btn-sm">Modifier</a>
        </div>

        <div class="detail-row">
            <span class="detail-row-label">Produit</span>
            <span class="detail-row-value">{{ $production->produit->nom ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Sacs de farine</span>
            <span class="detail-row-value">{{ \App\Support\Nombre::qte($production->nombre_sacs) }} sacs ({{ \App\Support\Nombre::qte($production->quantite_farine) }} kg)</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Paquets de levure</span>
            <span class="detail-row-value">{{ \App\Support\Nombre::qte($production->quantite_levure) }} kg</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Pains produits</span>
            <span class="detail-row-value" style="font-size:20px;font-weight:700;color:var(--brand)">
                {{ number_format($production->nombre_pains_produits, 0, ',', ' ') }}
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Enregistré le</span>
            <span class="detail-row-value">{{ $production->created_at->format('d/m/Y à H:i') }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('productions.destroy', $production) }}"
          onsubmit="return confirm('Supprimer cette production ? Le stock sera restauré.')">
        @csrf @method('DELETE')
        <button type="submit" class="btn" style="background:#fff;border:1px solid #e53e3e;color:#e53e3e;width:100%;margin-top:8px">
            Supprimer la production
        </button>
    </form>

@endsection
