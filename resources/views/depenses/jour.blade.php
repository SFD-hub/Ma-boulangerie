@extends('layouts.app')

@section('title', 'Dépenses du jour')

@section('content')

    <a href="{{ route('dashboard') }}" class="back-link">← Accueil</a>

    <h1 class="section-title" style="margin:12px 0 16px">Dépenses du jour</h1>

    {{-- Choix de la date --}}
    <div style="margin-bottom:16px">
        <input type="date" value="{{ $date }}" max="{{ date('Y-m-d') }}"
               onchange="window.location = '{{ route('depenses.jour') }}?date=' + this.value"
               style="padding:8px 12px;border-radius:10px;border:1px solid var(--border);font-size:13px;color:#111827;background:#FFFFFF">
    </div>

    {{-- ── Carte total ── --}}
    <div style="background:#EDE9FE;border-radius:18px;padding:18px 14px;margin-bottom:20px">
        <div style="font-size:12px;font-weight:600;color:#6B7280;margin-bottom:10px;line-height:1.3">
            Total dépenses
        </div>
        <div style="font-size:34px;font-weight:800;color:#111827;line-height:1">
            {{ number_format($totalDepense, 0, ',', ' ') }}
        </div>
        <div style="font-size:13px;color:#9CA3AF;margin-top:5px;font-weight:500">FCFA</div>
    </div>

    {{-- ── Détail ── --}}
    @if($depenses->isEmpty())
        <div class="card">
            <div class="empty-state">
                <p style="font-size:32px;margin-bottom:8px">💸</p>
                <p>Aucune dépense le {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.</p>
            </div>
        </div>
    @else
        <div class="card">
            @foreach($depenses as $depense)
                <a href="{{ route('depenses.show', $depense) }}" class="list-item" style="text-decoration:none">
                    <x-depense-icone :categorie="$depense->categorie" />
                    <div class="list-item-body">
                        <div class="list-item-name">{{ $depense->libelle }}</div>
                    </div>
                    <div class="list-item-right">
                        <span style="font-size:14px;font-weight:700;color:#111827">
                            {{ number_format($depense->montant, 0, ',', ' ') }}&nbsp;FCFA
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

@endsection
