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
    @if(session('success'))
        <div id="flash-ok"
             style="background:#ECFDF5;border:1px solid #A7F3D0;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:14px;color:#065F46;display:flex;align-items:center;gap:8px">
            <span style="font-size:18px">✅</span> {{ session('success') }}
        </div>
        <script>setTimeout(function(){var el=document.getElementById('flash-ok');if(el){el.style.transition='opacity .5s';el.style.opacity='0';setTimeout(function(){el.remove()},500)}},3000)</script>
    @endif

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
                @php
                    $b = (float) $bilan->benefice;
                    $estBenefice = $b > 0;
                    $estPerte    = $b < 0;
                @endphp
                <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB' }}">

                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="width:40px;height:40px;border-radius:12px;background:{{ $estBenefice ? '#ECFDF5' : ($estPerte ? '#FEF2F2' : '#F9FAFB') }};display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">
                            {{ $estBenefice ? '📈' : ($estPerte ? '📉' : '➖') }}
                        </div>
                        <div>
                            <div style="font-size:14px;font-weight:700;color:#111827">{{ $bilan->periodeLabel() }}</div>
                            <div style="font-size:12px;font-weight:600;margin-top:2px;color:{{ $estBenefice ? '#059669' : ($estPerte ? '#EF4444' : '#6B7280') }}">
                                @if($estBenefice)
                                    Bénéfice : +{{ number_format($b, 0, ',', ' ') }} FCFA
                                @elseif($estPerte)
                                    Perte : {{ number_format($b, 0, ',', ' ') }} FCFA
                                @else
                                    Équilibre : 0 FCFA
                                @endif
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('bilan.show', $bilan) }}"
                       style="font-size:13px;font-weight:700;color:#F97316;text-decoration:none;padding:8px 14px;border:1.5px solid #F97316;border-radius:10px;flex-shrink:0">
                        Voir
                    </a>

                </div>
            @endforeach

        </div>

    @endif

@endsection
