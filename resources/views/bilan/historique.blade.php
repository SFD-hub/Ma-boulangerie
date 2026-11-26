@extends('layouts.app')

@section('title', 'Historique des bilans')

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('bilan.index') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Historique des bilans</span>
        <div style="width:36px"></div>
    </div>

    {{-- ── Flash ── --}}

    @if($bilans->isEmpty())

        <div style="text-align:center;padding:60px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">📊</div>
            Aucun bilan enregistré.
            <div style="margin-top:16px">
                <a href="{{ route('bilan.index') }}"
                   style="font-size:14px;font-weight:600;color:#F97316;text-decoration:none">
                    Générer un bilan →
                </a>
            </div>
        </div>

    @else

        <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07)">

            @foreach($bilans as $bilan)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB' }}">

                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="width:40px;height:40px;border-radius:12px;background:#F9FAFB;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">
                            📊
                        </div>
                        <div>
                            <div style="font-size:14px;font-weight:700;color:#111827">{{ $bilan->periodeLabel() }}</div>
                        </div>
                    </div>

                    <a href="{{ route('bilan.show', $bilan) }}"
                       style="font-size:13px;font-weight:700;color:#F97316;text-decoration:none;padding:8px 14px;border:1.5px solid #F97316;border-radius:10px;flex-shrink:0">
                        Voir
                    </a>

                </div>
            @endforeach

        </div>

        @include('partials.pagination', ['paginator' => $bilans])

    @endif

@endsection
