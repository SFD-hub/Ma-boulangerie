@extends('layouts.app')

@section('title', 'Super Admin — Boulangeries')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
    <a href="{{ route('super-admin.dashboard') }}"
       style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <span style="font-size:17px;font-weight:700;color:#111827">Boulangeries</span>
    <div style="width:36px"></div>
</div>

{{-- Compteur --}}
<div style="font-size:13px;color:var(--text2);margin-bottom:12px;text-align:right">
    {{ $totalBoulangeries }} boulangerie{{ $totalBoulangeries > 1 ? 's' : '' }}
    · {{ $groupes->count() }} propriétaire{{ $groupes->count() > 1 ? 's' : '' }}
</div>

@if($totalBoulangeries === 0)
    <div class="card" style="text-align:center;padding:40px 20px;color:var(--text2)">
        <div style="font-size:40px;margin-bottom:12px">🏪</div>
        Aucune boulangerie enregistrée.
    </div>
@else

    @foreach($groupes as $groupe)
        @php $proprietaire = $groupe['proprietaire']; @endphp
        <div style="margin-bottom:18px">
            {{-- En-tête propriétaire --}}
            <div style="display:flex;align-items:center;gap:10px;padding:0 4px;margin-bottom:8px">
                <div style="width:30px;height:30px;border-radius:50%;background:var(--orange-soft);color:var(--orange-dark);
                            display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0">
                    {{ strtoupper(substr($proprietaire->name, 0, 1)) }}
                </div>
                <div style="flex:1;min-width:0">
                    <span style="font-size:14px;font-weight:700;color:#111827">{{ $proprietaire->name }}</span>
                    @if($proprietaire->telephone)
                        <span style="font-size:12px;color:var(--text2)">· {{ $proprietaire->telephone }}</span>
                    @endif
                </div>
                <span class="badge badge-orange" style="flex-shrink:0">
                    {{ $groupe['boulangeries']->count() }} boulangerie{{ $groupe['boulangeries']->count() > 1 ? 's' : '' }}
                </span>
            </div>

            {{-- Boulangeries de ce propriétaire --}}
            <div style="background:#FFFFFF;border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);margin-left:14px;border-left:2px solid var(--orange-soft)">
                @foreach($groupe['boulangeries'] as $b)
                    <a href="{{ route('super-admin.boulangeries.show', $b) }}"
                       style="display:flex;align-items:center;gap:14px;padding:13px 16px;
                              {{ $loop->last ? '' : 'border-bottom:1px solid var(--border);' }}
                              text-decoration:none;color:inherit">

                        <div style="width:40px;height:40px;background:var(--orange-bg);border-radius:11px;
                                    display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0">
                            🥖
                        </div>

                        <div style="flex:1;min-width:0">
                            <div style="font-size:14px;font-weight:700;color:#111827;
                                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                {{ $b->nom }}
                            </div>
                            <div style="font-size:12px;color:var(--text2);margin-top:1px">
                                📅 {{ $b->created_at ? $b->created_at->format('d/m/Y') : '—' }}
                                @if($b->telephone)
                                    · 📞 {{ $b->telephone }}
                                @endif
                            </div>
                        </div>

                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                             viewBox="0 0 24 24" style="color:var(--text2);flex-shrink:0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach

    @if($sansProprietaire->isNotEmpty())
        <div style="margin-bottom:18px">
            <div style="display:flex;align-items:center;gap:10px;padding:0 4px;margin-bottom:8px">
                <span style="font-size:16px">⚠️</span>
                <span style="font-size:14px;font-weight:700;color:var(--red)">Sans propriétaire</span>
                <span class="badge badge-red" style="margin-left:auto">
                    {{ $sansProprietaire->count() }}
                </span>
            </div>
            <div style="background:#FFFFFF;border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);margin-left:14px;border-left:2px solid var(--red)">
                @foreach($sansProprietaire as $b)
                    <a href="{{ route('super-admin.boulangeries.show', $b) }}"
                       style="display:flex;align-items:center;gap:14px;padding:13px 16px;
                              {{ $loop->last ? '' : 'border-bottom:1px solid var(--border);' }}
                              text-decoration:none;color:inherit">

                        <div style="width:40px;height:40px;background:var(--red-bg);border-radius:11px;
                                    display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0">
                            🥖
                        </div>

                        <div style="flex:1;min-width:0">
                            <div style="font-size:14px;font-weight:700;color:#111827;
                                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                {{ $b->nom }}
                            </div>
                            <div style="font-size:12px;color:var(--text2);margin-top:1px">
                                📅 {{ $b->created_at ? $b->created_at->format('d/m/Y') : '—' }}
                                @if($b->telephone)
                                    · 📞 {{ $b->telephone }}
                                @endif
                            </div>
                        </div>

                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                             viewBox="0 0 24 24" style="color:var(--text2);flex-shrink:0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

@endif

@endsection
