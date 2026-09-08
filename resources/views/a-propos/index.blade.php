@extends('layouts.app')

@section('title', 'À propos')

@section('content')

@php
    /* ── Informations facilement modifiables ── */
    $telephone  = '+221 784322880';
    $whatsappUrl = 'https://wa.me/221784322880'; // Remplacer XXXXXXXXX par le vrai numéro
    $email      = 'diopcheikhfallou3@gmail.com';
    $version    = '1.0.0';
@endphp

{{-- ── En-tête ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
    <a href="{{ route('dashboard') }}"
       style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#111827;text-decoration:none">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <span style="font-size:17px;font-weight:700;color:#111827">À propos</span>
    <div style="width:36px"></div>
</div>

{{-- ── Carte : L'application ── --}}
<div class="card" style="text-align:center;padding:28px 20px 22px">
    <div style="width:72px;height:72px;background:var(--orange-bg);border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width:48px;height:48px;object-fit:contain">
    </div>
    <div style="font-size:20px;font-weight:800;color:#111827;margin-bottom:10px">
        Ma Boulangerie
    </div>
    <p style="font-size:14px;color:var(--text2);line-height:1.6;max-width:340px;margin:0 auto">
        Ma Boulangerie est une application de gestion destinée aux boulangeries.
        Elle permet de gérer la production, le stock, les ventes, les versements,
        les dépenses et le suivi quotidien de l'activité.
    </p>
</div>

{{-- ── Carte : Développeur ── --}}
<div class="card">
    <div class="card-subtitle">Développé par</div>
    <div style="display:flex;align-items:center;gap:14px">
        <div style="width:48px;height:48px;background:#EFF6FF;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">
            👨‍💻
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:#111827">Serigne Fallou Diop</div>
            <div style="font-size:13px;color:var(--text2);margin-top:2px">Développeur de l'application</div>
        </div>
    </div>
</div>

{{-- ── Carte : Contact ── --}}
<div class="card">
    <div class="card-subtitle">Contact</div>

    <div class="detail-row">
        <div style="display:flex;align-items:center;gap:10px;color:var(--text2);font-size:14px">
            <span style="font-size:18px">📞</span>
            Téléphone
        </div>
        <div style="font-weight:600;font-size:14px;color:#111827">{{ $telephone }}</div>
    </div>

    <div class="detail-row" style="border-bottom:none">
        <div style="display:flex;align-items:center;gap:10px;color:var(--text2);font-size:14px">
            <span style="font-size:18px">✉️</span>
            E-mail
        </div>
        <div style="font-weight:600;font-size:14px;color:#111827">{{ $email }}</div>
    </div>
</div>

{{-- ── Carte : Version ── --}}
<div class="card">
    <div class="detail-row" style="border-bottom:none">
        <div style="display:flex;align-items:center;gap:10px;color:var(--text2);font-size:14px">
            <span style="font-size:18px">ℹ️</span>
            Version
        </div>
        <span class="badge badge-orange">v{{ $version }}</span>
    </div>
</div>

{{-- ── Bouton Contacter le support (prêt pour WhatsApp) ── --}}
<a href="{{ $whatsappUrl }}"
   target="_blank"
   rel="noopener noreferrer"
   style="display:flex;align-items:center;justify-content:center;gap:10px;
          width:100%;padding:15px 20px;border-radius:14px;
          background:#25D366;color:#FFFFFF;
          font-size:15px;font-weight:700;text-decoration:none;
          box-shadow:0 2px 8px rgba(37,211,102,.30);margin-bottom:8px">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
    Contacter le support
</a>

@endsection
