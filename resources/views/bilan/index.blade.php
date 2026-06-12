@extends('layouts.app')

@section('title', 'Bilan financier')

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('dashboard') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Bilan financier</span>
        <a href="{{ route('bilan.historique') }}"
           style="font-size:13px;font-weight:600;color:#F97316;text-decoration:none">
            Historique
        </a>
    </div>

    {{-- ── Formulaire de période ── --}}
    <div style="background:#FFFFFF;border-radius:18px;padding:20px 16px;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">

        <div style="font-size:13px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px">
            Sélectionner une période
        </div>

        <form method="GET" action="{{ route('bilan.detail') }}">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">

                {{-- Mois --}}
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#6B7280;margin-bottom:6px">Mois</label>
                    <select name="mois"
                            style="width:100%;padding:12px 10px;border:1.5px solid #E5E7EB;border-radius:10px;font-size:14px;background:#FFFFFF;color:#111827;font-family:inherit">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $m == $mois ? 'selected' : '' }}>
                                {{ ucfirst(\Carbon\Carbon::create()->month($m)->locale('fr')->monthName) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Année --}}
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#6B7280;margin-bottom:6px">Année</label>
                    <select name="annee"
                            style="width:100%;padding:12px 10px;border:1.5px solid #E5E7EB;border-radius:10px;font-size:14px;background:#FFFFFF;color:#111827;font-family:inherit">
                        @foreach($annees as $a)
                            <option value="{{ $a }}" {{ $a == $annee ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            <button type="submit"
                    style="display:block;width:100%;padding:15px;border-radius:14px;font-size:15px;font-weight:700;border:none;cursor:pointer;background:#F97316;color:#FFFFFF">
                Générer le bilan
            </button>
        </form>

    </div>

    {{-- ── Lien historique ── --}}
    <a href="{{ route('bilan.historique') }}"
       style="display:flex;align-items:center;justify-content:space-between;background:#FFFFFF;border-radius:14px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.07);text-decoration:none">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:40px;height:40px;border-radius:12px;background:#FFF7ED;display:flex;align-items:center;justify-content:center;font-size:19px">
                📁
            </div>
            <div>
                <div style="font-size:14px;font-weight:700;color:#111827">Historique des bilans</div>
                <div style="font-size:12px;color:#9CA3AF;margin-top:1px">Consulter les bilans enregistrés</div>
            </div>
        </div>
        <svg width="18" height="18" fill="none" stroke="#9CA3AF" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </a>

@endsection
