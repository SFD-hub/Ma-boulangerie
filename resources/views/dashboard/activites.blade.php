@extends('layouts.app')

@section('title', 'Historique des activités')

@section('content')

    {{-- ── En-tête ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('dashboard') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Historique des activités</span>
        <div style="width:36px"></div>
    </div>

    @if($activites->isEmpty())

        <div style="text-align:center;padding:60px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">📋</div>
            Aucune activité enregistrée.
        </div>

    @else

        {{-- ── Liste complète ── --}}
        <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:16px">

            @foreach($activites as $a)
                @php
                    $iconBg = match($a['icon']) {
                        '🍞' => '#ECFDF5',
                        '📦' => '#FFF3E0',
                        '💰' => '#EDE9FE',
                        '💸' => '#FEF3C7',
                        '👤' => '#EFF6FF',
                        '👔' => '#F0FDF4',
                        default => '#F3F4F6',
                    };
                @endphp
                <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">

                    {{-- Icône --}}
                    <div style="width:44px;height:44px;border-radius:11px;background:{{ $iconBg }};display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
                        {{ $a['icon'] }}
                    </div>

                    {{-- Libellé + détail --}}
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $a['label'] }}
                        </div>
                        <div style="font-size:12px;color:#9CA3AF;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $a['sub'] }}
                        </div>
                    </div>

                    {{-- Date + heure --}}
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-size:12px;font-weight:600;color:#6B7280">
                            {{ $a['at']->format('d/m/Y') }}
                        </div>
                        <div style="font-size:11px;color:#9CA3AF;margin-top:2px">
                            {{ $a['at']->format('H:i') }}
                        </div>
                    </div>

                </div>
            @endforeach

        </div>

        {{-- ── Pagination ── --}}
        @if($activites->hasPages())
            <div style="display:flex;align-items:center;justify-content:space-between;padding:4px 0 8px">

                @if($activites->onFirstPage())
                    <span style="font-size:14px;font-weight:600;color:#D1D5DB;padding:10px 16px;background:#F9FAFB;border-radius:10px">
                        ← Précédent
                    </span>
                @else
                    <a href="{{ $activites->previousPageUrl() }}"
                       style="font-size:14px;font-weight:600;color:#F97316;padding:10px 16px;background:#FFF7ED;border-radius:10px;text-decoration:none">
                        ← Précédent
                    </a>
                @endif

                <span style="font-size:13px;color:#9CA3AF;font-weight:500">
                    Page {{ $activites->currentPage() }} / {{ $activites->lastPage() }}
                </span>

                @if($activites->hasMorePages())
                    <a href="{{ $activites->nextPageUrl() }}"
                       style="font-size:14px;font-weight:600;color:#F97316;padding:10px 16px;background:#FFF7ED;border-radius:10px;text-decoration:none">
                        Suivant →
                    </a>
                @else
                    <span style="font-size:14px;font-weight:600;color:#D1D5DB;padding:10px 16px;background:#F9FAFB;border-radius:10px">
                        Suivant →
                    </span>
                @endif

            </div>

            <div style="text-align:center;margin-bottom:8px">
                <span style="font-size:12px;color:#9CA3AF">
                    {{ $activites->total() }} activité{{ $activites->total() > 1 ? 's' : '' }} au total
                </span>
            </div>
        @endif

    @endif

@endsection
