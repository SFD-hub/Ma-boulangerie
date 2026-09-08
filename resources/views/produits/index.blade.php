@extends('layouts.app')

@section('title', 'Produits')

@section('content')

    <div class="section-actions">
        <h1 class="section-title">Produits</h1>
        <a href="{{ route('produits.create') }}" class="btn btn-primary btn-sm">+ Ajouter</a>
    </div>

    @if($produits->isEmpty())
        <div class="card">
            <div class="empty-state">
                <p style="font-size:32px;margin-bottom:8px">🍞</p>
                <p>Aucun produit enregistré.</p>
                <a href="{{ route('produits.create') }}" class="btn btn-primary btn-sm" style="margin-top:14px">
                    + Ajouter un produit
                </a>
            </div>
        </div>
    @else
        <div class="card">
            @foreach($produits as $produit)
                <div class="list-item">
                    <div class="list-item-body">
                        <div class="list-item-name">{{ $produit->nom }}</div>
                    </div>
                    <div class="list-item-right" style="display:flex;align-items:center;gap:10px">
                        <span class="badge {{ $produit->actif ? 'badge-green' : 'badge-gray' }}">
                            {{ $produit->actif ? 'Actif' : 'Inactif' }}
                        </span>
                        <a href="{{ route('produits.edit', $produit) }}"
                           style="font-size:13px;font-weight:600;color:#0f766e;text-decoration:none">
                            Modifier
                        </a>
                        @if($produit->actif)
                            <form method="POST" action="{{ route('produits.desactiver', $produit) }}">
                                @csrf @method('PATCH')
                                <button type="submit" style="background:none;border:none;color:#c33;font-size:13px;font-weight:600;cursor:pointer;padding:0">
                                    Désactiver
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('produits.reactiver', $produit) }}">
                                @csrf @method('PATCH')
                                <button type="submit" style="background:none;border:none;color:#059669;font-size:13px;font-weight:600;cursor:pointer;padding:0">
                                    Réactiver
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection
