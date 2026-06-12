@extends('layouts.app')

@section('title', 'Détail gérant')

@section('content')

    {{-- ── En-tête page ── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <a href="{{ route('gerants.index') }}"
           style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span style="font-size:17px;font-weight:700;color:#111827">Détail gérant</span>
        <div style="width:36px"></div>
    </div>

    {{-- ── Messages flash ── --}}
    @if(session('success'))
        <div id="flash-ok"
             style="background:#ECFDF5;border:1px solid #A7F3D0;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:14px;color:#065F46;display:flex;align-items:center;gap:8px">
            <span style="font-size:18px">✅</span> {{ session('success') }}
        </div>
        <script>setTimeout(function(){var el=document.getElementById('flash-ok');if(el){el.style.transition='opacity .5s';el.style.opacity='0';setTimeout(function(){el.remove()},500)}},3000)</script>
    @endif

    @if(session('warning'))
        <div style="background:#FFFBEB;border:1px solid #FCD34D;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:14px;color:#92400E;display:flex;align-items:center;gap:8px">
            <span style="font-size:18px">⚠️</span> {{ session('warning') }}
        </div>
    @endif

    {{-- ── Carte profil ── --}}
    <div style="background:#FFFFFF;border-radius:18px;padding:28px 20px 24px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.07);margin-bottom:20px">

        {{-- Avatar --}}
        <div style="width:72px;height:72px;border-radius:36px;background:#F3F4F6;display:flex;align-items:center;justify-content:center;font-size:30px;font-weight:700;color:#6B7280;margin:0 auto 14px">
            {{ strtoupper(substr($gerant->name, 0, 1)) }}
        </div>

        {{-- Nom --}}
        <div style="font-size:20px;font-weight:800;color:#111827;margin-bottom:6px">
            {{ $gerant->name }}
        </div>

        {{-- Téléphone --}}
        @if($gerant->telephone)
            <div style="font-size:14px;color:#9CA3AF;margin-bottom:4px">
                {{ $gerant->telephone }}
            </div>
        @endif

        {{-- Email (si renseigné) --}}
        @if($gerant->email)
            <div style="font-size:14px;color:#9CA3AF;margin-bottom:4px">
                {{ $gerant->email }}
            </div>
        @endif

        {{-- Statut --}}
        <div style="margin-top:12px">
            <span style="display:inline-block;padding:4px 16px;border-radius:99px;font-size:13px;font-weight:700;{{ $gerant->actif ? 'background:#ECFDF5;color:#059669' : 'background:#FEF2F2;color:#EF4444' }}">
                {{ $gerant->actif ? 'Actif' : 'Inactif' }}
            </span>
        </div>

    </div>

    {{-- ── Actions ── --}}
    <div style="display:flex;flex-direction:column;gap:10px">

        {{-- Modifier --}}
        <a href="{{ route('gerants.edit', $gerant) }}"
           style="display:block;text-align:center;padding:15px;border-radius:14px;font-size:15px;font-weight:700;text-decoration:none;border:2px solid #F97316;color:#F97316;background:#FFFFFF">
            Modifier
        </a>

        {{-- Désactiver / Activer --}}
        @if($gerant->actif)
            <form method="POST" action="{{ route('gerants.desactiver', $gerant) }}">
                @csrf @method('PATCH')
                <button type="submit"
                        style="display:block;width:100%;padding:15px;border-radius:14px;font-size:15px;font-weight:700;border:none;cursor:pointer;background:#FFF7ED;color:#F97316">
                    Désactiver
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('gerants.activer', $gerant) }}">
                @csrf @method('PATCH')
                <button type="submit"
                        style="display:block;width:100%;padding:15px;border-radius:14px;font-size:15px;font-weight:700;border:none;cursor:pointer;background:#ECFDF5;color:#059669">
                    Activer
                </button>
            </form>
        @endif

        {{-- Supprimer --}}
        <form method="POST" action="{{ route('gerants.destroy', $gerant) }}"
              onsubmit="return confirm('Supprimer définitivement ce gérant ? Cette action est irréversible.')">
            @csrf @method('DELETE')
            <button type="submit"
                    style="display:block;width:100%;padding:15px;border-radius:14px;font-size:15px;font-weight:700;border:2px solid #EF4444;background:#FFFFFF;color:#EF4444;cursor:pointer">
                Supprimer
            </button>
        </form>

    </div>

@endsection
