@extends('layouts.app')

@section('title', 'Gestion gérants')

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('dashboard') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Gestion gérants</span>
        <a href="{{ route('gerants.create') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none;font-size:24px;font-weight:300;line-height:1">
            +
        </a>
    </div>

    @if($gerants->isEmpty())

        <div style="text-align:center;padding:60px 0;color:#9CA3AF;font-size:14px">
            <div style="font-size:40px;margin-bottom:12px">👤</div>
            Aucun gérant enregistré.
            <div style="margin-top:16px">
                <a href="{{ route('gerants.create') }}"
                   style="display:inline-flex;align-items:center;gap:6px;background:#F97316;color:#FFFFFF;border-radius:12px;padding:12px 20px;font-size:14px;font-weight:700;text-decoration:none">
                    + Créer un gérant
                </a>
            </div>
        </div>

    @else

        <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07)">
            @foreach($gerants as $gerant)
                <a href="{{ route('gerants.show', $gerant) }}"
                   style="display:flex;align-items:center;gap:14px;padding:16px 16px;text-decoration:none;{{ $loop->last ? '' : 'border-bottom:1px solid #E5E7EB;' }}">

                    {{-- Avatar --}}
                    <div style="width:46px;height:46px;border-radius:23px;background:#F3F4F6;display:flex;align-items:center;justify-content:center;font-size:19px;font-weight:700;color:#6B7280;flex-shrink:0">
                        {{ strtoupper(substr($gerant->name, 0, 1)) }}
                    </div>

                    {{-- Infos --}}
                    <div style="flex:1;min-width:0">
                        <div style="font-size:15px;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $gerant->name }}
                        </div>
                        <div style="font-size:13px;color:#9CA3AF;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $gerant->telephone ?? $gerant->email ?? '—' }}
                        </div>
                    </div>

                    {{-- Statut --}}
                    <span style="font-size:13px;font-weight:700;color:{{ $gerant->actif ? '#10B981' : '#EF4444' }};flex-shrink:0">
                        {{ $gerant->actif ? 'Actif' : 'Inactif' }}
                    </span>

                </a>
            @endforeach
        </div>

    @endif

@endsection
