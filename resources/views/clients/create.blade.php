@extends('layouts.app')

@section('title', 'Ajouter')

@section('content')

    @php
        $typeUrl = in_array(request('type'), ['livreur', 'client', 'vendeur'], true) ? request('type') : 'livreur';
        $choix   = old('type_choice', old('type', old('adresse') !== null ? 'abonne' : $typeUrl));
        // Ajout d'un vendeur depuis la Boutique : le type est fixé, aucun choix à faire.
        $modeVendeur = $typeUrl === 'vendeur' || $choix === 'vendeur';
    @endphp

    <a href="{{ $modeVendeur ? route('depot.index') : route('clients.index') }}" class="back-link">← {{ $modeVendeur ? 'Boutique' : 'Clients' }}</a>

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-title">{{ $modeVendeur ? 'Ajouter un vendeur' : 'Ajouter' }}</div>

        @if($modeVendeur)
            <div style="display:none">
                <input type="radio" name="type_choice" value="vendeur" checked>
            </div>
        @else
        <div class="form-group">
            <label class="form-label">Type *</label>
            <div style="display:flex;gap:20px;padding-top:4px;flex-wrap:wrap">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
                    <input type="radio" name="type_choice" value="livreur" onchange="updateAddForm()"
                           {{ $choix === 'livreur' ? 'checked' : '' }}>
                    <span>Livreur</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
                    <input type="radio" name="type_choice" value="client" onchange="updateAddForm()"
                           {{ $choix === 'client' ? 'checked' : '' }}>
                    <span>Client</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
                    <input type="radio" name="type_choice" value="abonne" onchange="updateAddForm()"
                           {{ $choix === 'abonne' ? 'checked' : '' }}>
                    <span>Abonné</span>
                </label>
            </div>
        </div>
        @endif

        <form id="addForm" method="POST" action="{{ route('livreurs.store') }}">
            @csrf
            <input type="hidden" name="type" id="hiddenType" value="livreur">

            <div class="form-group">
                <label class="form-label">Prénom et nom *</label>
                <input class="form-input" type="text" name="nom_complet"
                       value="{{ old('nom_complet') }}" placeholder="Ex : Moussa Diallo" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Téléphone *</label>
                <input class="form-input" type="text" name="telephone"
                       value="{{ old('telephone') }}" placeholder="77 000 00 00" required>
            </div>

            <div class="form-group" id="adresseGroup" style="display:none">
                <label class="form-label">Adresse</label>
                <input class="form-input" type="text" name="adresse"
                       value="{{ old('adresse') }}" placeholder="Quartier, ville">
            </div>

            <button type="submit" class="btn btn-primary btn-full">Ajouter</button>
        </form>
    </div>

    <script>
        function updateAddForm() {
            var choice = document.querySelector('input[name="type_choice"]:checked').value;
            var form = document.getElementById('addForm');
            var hiddenType = document.getElementById('hiddenType');
            var adresseGroup = document.getElementById('adresseGroup');

            if (choice === 'abonne') {
                form.action = "{{ route('clients-abonnes.store') }}";
                adresseGroup.style.display = 'block';
            } else {
                form.action = "{{ route('livreurs.store') }}";
                hiddenType.value = choice;
                adresseGroup.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', updateAddForm);
    </script>

@endsection
