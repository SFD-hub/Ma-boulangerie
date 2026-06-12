@extends('layouts.app')

@section('title', 'Ajouter consommation')

@section('content')

    {{-- ── En-tête ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('clients-abonnes.show', $clientAbonne) }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Ajouter consommation</span>
        <div style="width:36px"></div>
    </div>

    {{-- ── Erreurs de validation ── --}}
    @if($errors->any())
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:12px;padding:14px 16px;margin-bottom:16px">
            @foreach($errors->all() as $msg)
                <div style="font-size:13px;color:#EF4444;padding:2px 0">{{ $msg }}</div>
            @endforeach
        </div>
    @endif

    <div style="background:#FFFFFF;border-radius:18px;padding:20px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07)">

        <form method="POST" action="{{ route('clients-abonnes.consommations.store', $clientAbonne) }}">
            @csrf

            {{-- Abonné (affichage) --}}
            <div style="margin-bottom:18px">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Abonné</label>
                <div style="background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:13px 16px;font-size:15px;font-weight:600;color:#111827;display:flex;align-items:center;gap:10px">
                    <div style="width:32px;height:32px;border-radius:8px;background:#F97316;color:#FFFFFF;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0">
                        {{ strtoupper(substr($clientAbonne->prenom, 0, 1)) }}
                    </div>
                    {{ $clientAbonne->prenom }} {{ $clientAbonne->nom }}
                </div>
            </div>

            {{-- Date --}}
            <div style="margin-bottom:18px">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Date *</label>
                <input type="date" name="date_consommation"
                       value="{{ old('date_consommation', date('Y-m-d')) }}"
                       max="{{ date('Y-m-d') }}" required
                       style="width:100%;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;font-size:15px;color:#111827;box-sizing:border-box;outline:none;-webkit-appearance:none">
            </div>

            {{-- Nombre de pains --}}
            <div style="margin-bottom:24px">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Nombre de pains *</label>
                <input type="number" name="quantite" min="1"
                       value="{{ old('quantite') }}" placeholder="Ex: 2" required
                       style="width:100%;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:14px 16px;font-size:15px;color:#111827;box-sizing:border-box;outline:none">
            </div>

            <button type="submit"
                    style="width:100%;background:#F97316;color:#FFFFFF;border:none;border-radius:14px;padding:16px 20px;font-size:16px;font-weight:700;cursor:pointer">
                Enregistrer
            </button>

        </form>
    </div>

@endsection
