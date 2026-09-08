@extends('layouts.app')

@section('title', 'Super Admin — Sauvegardes')

@section('content')

{{-- En-tête --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
    <a href="{{ route('super-admin.dashboard') }}"
       style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <span style="font-size:17px;font-weight:700;color:#111827">Sauvegardes</span>
    <div style="width:36px"></div>
</div>

{{-- Messages flash --}}
@if(session('success'))
    <div style="background:#ECFDF5;border:1px solid #6EE7B7;border-radius:10px;
                padding:12px 16px;margin-bottom:16px;font-size:14px;
                color:#065F46;display:flex;align-items:center;gap:10px">
        <span style="font-size:18px;flex-shrink:0">✅</span>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#FEF2F2;border:1px solid #FCA5A5;border-radius:10px;
                padding:12px 16px;margin-bottom:16px;font-size:14px;
                color:#991B1B;display:flex;align-items:flex-start;gap:10px">
        <span style="font-size:18px;flex-shrink:0">⚠️</span>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- Bouton sauvegarde manuelle --}}
<div class="card" style="padding:16px;margin-bottom:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:3px">
                Sauvegarde manuelle
            </div>
            <div style="font-size:12px;color:var(--text2)">
                Crée immédiatement une sauvegarde complète de la base de données.
            </div>
        </div>
        <form method="POST" action="{{ route('super-admin.backups.create') }}"
              onsubmit="return confirm('Lancer une sauvegarde maintenant ?')">
            @csrf
            <button type="submit"
                    style="padding:10px 18px;background:var(--orange);color:#fff;border:none;
                           border-radius:50px;font-size:13px;font-weight:700;font-family:inherit;
                           cursor:pointer;display:flex;align-items:center;gap:6px;white-space:nowrap">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Sauvegarder maintenant
            </button>
        </form>
    </div>
    <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);
                font-size:12px;color:var(--text2);display:flex;align-items:center;gap:6px">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Sauvegarde automatique planifiée tous les jours à 02h00.
        Rétention : 30 sauvegardes maximum.
    </div>
</div>

{{-- Compteur --}}
<div style="font-size:13px;color:var(--text2);margin-bottom:10px;padding:0 2px">
    {{ count($files) }} sauvegarde{{ count($files) > 1 ? 's' : '' }}
    @if(count($files) > 0)
        <span style="color:var(--text2)">
            — {{ round(array_sum(array_column($files, 'size')) / 1024 / 1024, 1) }} Mo au total
        </span>
    @endif
</div>

{{-- Liste des sauvegardes --}}
@if(empty($files))
    <div class="card" style="text-align:center;padding:40px 20px;color:var(--text2)">
        <div style="font-size:40px;margin-bottom:12px">💾</div>
        <div style="font-size:15px;font-weight:600;color:#111827;margin-bottom:6px">
            Aucune sauvegarde
        </div>
        <div style="font-size:13px">
            Cliquez sur "Sauvegarder maintenant" pour créer la première sauvegarde.
        </div>
    </div>
@else
    <div style="background:#FFFFFF;border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow)">
        @foreach($files as $i => $file)
            @php
                $sizeHuman = $file['size'] >= 1024 * 1024
                    ? round($file['size'] / 1024 / 1024, 1) . ' Mo'
                    : round($file['size'] / 1024, 1) . ' Ko';

                // Extraire la date depuis le nom : backup_Y_m_d_His.sql
                $dateStr = '';
                if (preg_match('/backup_(\d{4})_(\d{2})_(\d{2})_(\d{2})(\d{2})(\d{2})\.sql/', $file['name'], $m)) {
                    $dateStr = "{$m[3]}/{$m[2]}/{$m[1]} à {$m[4]}:{$m[5]}:{$m[6]}";
                }
            @endphp

            <div style="padding:14px 16px;display:flex;align-items:center;gap:12px;
                        {{ $loop->last ? '' : 'border-bottom:1px solid var(--border);' }}">

                {{-- Icône --}}
                <div style="width:42px;height:42px;border-radius:12px;background:#EFF6FF;
                            display:flex;align-items:center;justify-content:center;
                            font-size:20px;flex-shrink:0">
                    💾
                </div>

                {{-- Infos --}}
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:700;color:#111827;
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        {{ $file['name'] }}
                    </div>
                    <div style="font-size:12px;color:var(--text2);margin-top:2px;display:flex;gap:10px;flex-wrap:wrap">
                        <span>🕐 {{ $dateStr }}</span>
                        <span>📦 {{ $sizeHuman }}</span>
                    </div>
                </div>

                {{-- Actions --}}
                <div style="display:flex;gap:6px;flex-shrink:0">

                    {{-- Télécharger --}}
                    <a href="{{ route('super-admin.backups.download', $file['name']) }}"
                       title="Télécharger"
                       style="width:34px;height:34px;border-radius:8px;border:1px solid var(--border);
                              background:#F9FAFB;display:flex;align-items:center;justify-content:center;
                              color:var(--text2);text-decoration:none"
                       onmouseover="this.style.borderColor='#3B82F6';this.style.color='#3B82F6'"
                       onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text2)'">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </a>

                    {{-- Supprimer --}}
                    <form method="POST"
                          action="{{ route('super-admin.backups.destroy', $file['name']) }}"
                          onsubmit="return confirm('Supprimer définitivement cette sauvegarde ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                title="Supprimer"
                                style="width:34px;height:34px;border-radius:8px;border:1px solid var(--border);
                                       background:#F9FAFB;cursor:pointer;display:flex;align-items:center;
                                       justify-content:center;color:var(--text2)"
                                onmouseover="this.style.borderColor='var(--red)';this.style.color='var(--red)'"
                                onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text2)'">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>

                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
