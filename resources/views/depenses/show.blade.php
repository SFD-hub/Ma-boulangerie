@extends('layouts.app')

@section('content')

<h2>Détail dépense</h2>

<p><strong>Libellé :</strong> {{ $depense->libelle }}</p>

<p><strong>Catégorie :</strong> {{ $depense->categorie }}</p>

<p><strong>Montant :</strong> {{ $depense->montant }} FCFA</p>

<p><strong>Date :</strong> {{ $depense->date_depense }}</p>

<a href="{{ route('depenses.index') }}">
    Retour
</a>

@endsection