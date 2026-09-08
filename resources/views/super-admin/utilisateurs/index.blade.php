@extends('layouts.app')

@section('title', 'Super Admin — Utilisateurs')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
    <a href="{{ route('super-admin.dashboard') }}"
       style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <span style="font-size:17px;font-weight:700;color:#111827">Utilisateurs</span>
    <div style="width:36px"></div>
</div>

{{-- Compteur --}}
<div style="font-size:13px;color:var(--text2);margin-bottom:12px;text-align:right">
    {{ $totalUtilisateurs }} utilisateur{{ $totalUtilisateurs > 1 ? 's' : '' }}
    · {{ $groupes->count() }} boulangerie{{ $groupes->count() > 1 ? 's' : '' }}
</div>

@if($totalUtilisateurs === 0)
    <div class="card" style="text-align:center;padding:40px 20px;color:var(--text2)">
        <div style="font-size:40px;margin-bottom:12px">👥</div>
        Aucun utilisateur enregistré.
    </div>
@else

    @foreach($groupes as $groupe)
        @php $b = $groupe['boulangerie']; @endphp
        <div style="margin-bottom:18px">
            {{-- En-tête boulangerie --}}
            <div style="display:flex;align-items:center;gap:10px;padding:0 4px;margin-bottom:8px">
                <div style="width:30px;height:30px;border-radius:9px;background:var(--orange-bg);
                            display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0">
                    🥖
                </div>
                <div style="flex:1;min-width:0">
                    <span style="font-size:14px;font-weight:700;color:#111827">{{ $b->nom }}</span>
                </div>
                <span class="badge badge-orange" style="flex-shrink:0">
                    {{ $groupe['membres']->count() }} utilisateur{{ $groupe['membres']->count() > 1 ? 's' : '' }}
                </span>
            </div>

            {{-- Utilisateurs de cette boulangerie --}}
            <div style="background:#FFFFFF;border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);margin-left:14px;border-left:2px solid var(--orange-soft)">
                @foreach($groupe['membres'] as $user)
                    <div style="padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid var(--border);' }}">

                        <div style="display:flex;align-items:center;gap:12px">
                            {{-- Avatar --}}
                            <div style="width:40px;height:40px;border-radius:50%;background:var(--orange-soft);
                                        display:flex;align-items:center;justify-content:center;
                                        font-size:15px;font-weight:700;color:var(--orange-dark);flex-shrink:0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>

                            {{-- Infos --}}
                            <div style="flex:1;min-width:0">
                                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                    <span style="font-size:14px;font-weight:700;color:#111827">
                                        {{ $user->name }}
                                    </span>
                                    @if($user->role)
                                        <span class="badge {{ $user->role->nom === 'proprietaire' ? 'badge-orange' : 'badge-blue' }}">
                                            {{ $user->role->nom === 'proprietaire' ? 'Propriétaire' : 'Gérant' }}
                                        </span>
                                    @endif
                                </div>

                                @if($user->email)
                                    <div style="font-size:12px;color:var(--text2);margin-top:2px">
                                        ✉️ {{ $user->email }}
                                    </div>
                                @endif

                                @if($user->telephone)
                                    <div style="font-size:12px;color:var(--text2);margin-top:1px">
                                        📞 {{ $user->telephone }}
                                    </div>
                                @endif

                                @if($user->role?->nom === 'proprietaire' && $user->boulangeries->count() > 1)
                                    <div style="font-size:12px;color:var(--text2);margin-top:1px">
                                        🏪 possède aussi : {{ $user->boulangeries->where('id', '!=', $b->id)->pluck('nom')->implode(', ') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Dernière connexion --}}
                        <div style="margin-top:8px;padding:6px 10px;background:#F9FAFB;border-radius:8px;
                                    font-size:12px;color:var(--text2)">
                            🕐 Dernière connexion :
                            @if($user->last_login_at)
                                <span style="font-weight:600;color:#111827">
                                    {{ $user->last_login_at->format('d/m/Y à H:i') }}
                                </span>
                            @else
                                <span style="color:var(--text2)">Jamais connecté</span>
                            @endif
                        </div>

                        {{-- Action réinitialisation --}}
                        <div style="margin-top:10px;text-align:right">
                            <a href="{{ route('super-admin.utilisateurs.reset-password.form', $user) }}"
                               style="display:inline-flex;align-items:center;gap:6px;font-size:12px;
                                      font-weight:600;color:var(--text2);text-decoration:none;
                                      padding:5px 10px;border:1px solid var(--border);border-radius:8px;
                                      background:#F9FAFB"
                               onmouseover="this.style.borderColor='var(--orange)';this.style.color='var(--orange)'"
                               onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text2)'">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                                Réinitialiser le mot de passe
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @if($sansBoulangerie->isNotEmpty())
        <div style="margin-bottom:18px">
            <div style="display:flex;align-items:center;gap:10px;padding:0 4px;margin-bottom:8px">
                <span style="font-size:16px">⚠️</span>
                <span style="font-size:14px;font-weight:700;color:var(--red)">Sans boulangerie</span>
                <span class="badge badge-red" style="margin-left:auto">
                    {{ $sansBoulangerie->count() }}
                </span>
            </div>
            <div style="background:#FFFFFF;border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);margin-left:14px;border-left:2px solid var(--red)">
                @foreach($sansBoulangerie as $user)
                    <div style="padding:14px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid var(--border);' }}">
                        <div style="display:flex;align-items:center;gap:12px">
                            <div style="width:40px;height:40px;border-radius:50%;background:var(--red-bg);
                                        display:flex;align-items:center;justify-content:center;
                                        font-size:15px;font-weight:700;color:var(--red);flex-shrink:0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div style="flex:1;min-width:0">
                                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                    <span style="font-size:14px;font-weight:700;color:#111827">{{ $user->name }}</span>
                                    @if($user->role)
                                        <span class="badge {{ $user->role->nom === 'proprietaire' ? 'badge-orange' : 'badge-blue' }}">
                                            {{ $user->role->nom === 'proprietaire' ? 'Propriétaire' : 'Gérant' }}
                                        </span>
                                    @endif
                                </div>
                                @if($user->telephone)
                                    <div style="font-size:12px;color:var(--text2);margin-top:2px">📞 {{ $user->telephone }}</div>
                                @endif
                            </div>
                        </div>
                        <div style="margin-top:10px;text-align:right">
                            <a href="{{ route('super-admin.utilisateurs.reset-password.form', $user) }}"
                               style="display:inline-flex;align-items:center;gap:6px;font-size:12px;
                                      font-weight:600;color:var(--text2);text-decoration:none;
                                      padding:5px 10px;border:1px solid var(--border);border-radius:8px;
                                      background:#F9FAFB">
                                Réinitialiser le mot de passe
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endif

@endsection
