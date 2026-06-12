<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'Boulangerie')</title>
    <style>
        :root {
            --orange:      #F97316;
            --orange-dark: #EA6C0A;
            --orange-bg:   #FFF7ED;
            --orange-soft: #FFEDD5;
            --blue-bg:     #EFF6FF;
            --blue:        #3B82F6;
            --green:       #10B981;
            --green-bg:    #ECFDF5;
            --red:         #EF4444;
            --red-bg:      #FEF2F2;
            --yellow:      #F59E0B;
            --yellow-bg:   #FFFBEB;
            --bg:          #F3F4F6;
            --white:       #FFFFFF;
            --text:        #111827;
            --text2:       #6B7280;
            --border:      #E5E7EB;
            --shadow:      0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.05);
            --shadow-md:   0 4px 6px rgba(0,0,0,.07), 0 2px 4px rgba(0,0,0,.05);
            --nav-h:       68px;
            --top-h:       64px;
            --radius:      14px;
            --radius-sm:   8px;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        html { -webkit-text-size-adjust:100%; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', sans-serif;
            font-size: 15px;
            line-height: 1.5;
            padding-bottom: calc(var(--nav-h) + env(safe-area-inset-bottom));
        }
        a { color: inherit; text-decoration: none; }
        button { font-family: inherit; }

        /* ── Topbar ── */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 100;
            height: var(--top-h);
            background: var(--white);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
        }
        .topbar-left { display:flex; align-items:center; gap:10px; }
        .topbar-icon {
            width: 40px; height: 40px;
            background: var(--orange-bg);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }
        .topbar-boulangerie {
            font-size: 13px; font-weight: 700;
            color: var(--orange); line-height: 1.2;
            text-transform: uppercase; letter-spacing: .02em;
        }
        .topbar-user { font-size: 12px; color: var(--text2); }

        /* ── Main wrapper ── */
        .main-wrap {
            max-width: 480px;
            margin: 0 auto;
            padding: 16px 14px 8px;
        }

        /* ── Responsive desktop ── */
        @media (min-width: 768px) {
            .topbar { padding: 0 32px; }
            .main-wrap { max-width: 520px; padding: 24px 0 8px; }
            .bottom-nav .nav-wrap { max-width: 520px; margin: 0 auto; }
        }
        @media (min-width: 1024px) {
            .main-wrap { max-width: 560px; }
            .bottom-nav .nav-wrap { max-width: 560px; }
        }

        /* ── Flash messages ── */
        .flash {
            padding: 12px 14px; border-radius: var(--radius-sm);
            margin-bottom: 14px; font-size: 14px; font-weight: 500;
        }
        .flash-success { background: var(--green-bg); color: #065F46; border: 1px solid #6EE7B7; }
        .flash-error   { background: var(--red-bg);   color: #991B1B; border: 1px solid #FCA5A5; }
        .flash-warning { background: var(--yellow-bg); color: #92400E; border: 1px solid #FCD34D; }

        /* ── Alert strip ── */
        .alert-strip {
            display: flex; align-items: center; gap: 10px;
            background: var(--yellow-bg); border: 1px solid #FCD34D;
            border-radius: var(--radius-sm); padding: 10px 14px;
            font-size: 13px; color: #92400E; margin-bottom: 10px;
        }

        /* ── Cards ── */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: var(--shadow);
        }
        .card-title {
            font-size: 16px; font-weight: 700;
            color: var(--text); margin-bottom: 14px;
        }
        .card-subtitle {
            font-size: 12px; color: var(--text2);
            text-transform: uppercase; letter-spacing: .06em;
            font-weight: 600; margin-bottom: 10px;
        }

        /* ── Stat grid ── */
        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 12px;
        }
        .stat-card {
            border-radius: var(--radius);
            padding: 16px 14px;
            position: relative;
            overflow: hidden;
        }
        .stat-card.orange   { background: var(--orange-bg); }
        .stat-card.blue     { background: var(--blue-bg); }
        .stat-card.bordered { background: var(--white); border-left: 4px solid var(--orange); box-shadow: var(--shadow); }
        .stat-label { font-size: 12px; font-weight: 600; color: var(--text2); margin-bottom: 6px; }
        .stat-value { font-size: 28px; font-weight: 800; line-height: 1; }
        .stat-value.orange { color: var(--orange); }
        .stat-value.blue   { color: var(--blue); }
        .stat-value.text   { color: var(--text); }
        .stat-unit { font-size: 13px; font-weight: 600; color: var(--text2); margin-top: 4px; }
        /* Alert on stat card */
        .stat-card.low .stat-value { color: var(--red); }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 12px 20px;
            border-radius: 50px;
            font-size: 14px; font-weight: 700;
            border: none; cursor: pointer;
            text-decoration: none; transition: opacity .15s;
        }
        .btn:hover { opacity: .88; }
        .btn-primary  { background: var(--orange); color: #fff; }
        .btn-secondary{ background: var(--white); color: var(--text); border: 1.5px solid var(--border); border-radius: var(--radius-sm); }
        .btn-danger   { background: var(--red);  color: #fff; }
        .btn-outline  { background: transparent; color: var(--orange); border: 1.5px solid var(--orange); }
        .btn-sm       { padding: 7px 14px; font-size: 13px; }
        .btn-full     { width: 100%; border-radius: var(--radius-sm); }
        .btn-icon     { width: 36px; height: 36px; padding: 0; border-radius: 50%; }

        /* ── Forms ── */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--text); }
        .form-input {
            width: 100%; padding: 12px 14px;
            border: 1.5px solid var(--border); border-radius: var(--radius-sm);
            font-size: 15px; background: var(--white); color: var(--text);
            transition: border-color .15s; font-family: inherit;
        }
        .form-input:focus { outline: none; border-color: var(--orange); }
        .form-input.readonly { background: #F9FAFB; color: var(--text2); }
        .form-error { font-size: 12px; color: var(--red); margin-top: 4px; }
        .form-hint  { font-size: 12px; color: var(--text2); margin-top: 4px; }
        .form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        /* ── Badges ── */
        .badge {
            display: inline-flex; align-items: center;
            padding: 3px 10px; border-radius: 50px;
            font-size: 12px; font-weight: 600;
        }
        .badge-green  { background: var(--green-bg);  color: #065F46; }
        .badge-red    { background: var(--red-bg);    color: #991B1B; }
        .badge-orange { background: var(--orange-soft); color: var(--orange-dark); }
        .badge-gray   { background: #F3F4F6; color: var(--text2); }
        .badge-blue   { background: var(--blue-bg); color: #1D4ED8; }

        /* ── List items ── */
        .list-item {
            display: flex; align-items: center;
            justify-content: space-between;
            padding: 13px 0;
            border-bottom: 1px solid var(--border);
            gap: 10px;
        }
        .list-item:last-child { border-bottom: none; }
        .list-item-icon {
            width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .list-item-body { flex: 1; min-width: 0; }
        .list-item-name { font-weight: 600; font-size: 15px; }
        .list-item-sub  { font-size: 13px; color: var(--text2); margin-top: 1px; }
        .list-item-right{ text-align: right; flex-shrink: 0; }
        .list-item-amount { font-weight: 700; font-size: 15px; }
        .list-item-date { font-size: 12px; color: var(--text2); }

        /* ── Avatar ── */
        .avatar {
            width: 44px; height: 44px; border-radius: 50%;
            background: var(--orange-soft); color: var(--orange-dark);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 700; flex-shrink: 0;
        }

        /* ── Detail rows ── */
        .detail-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 14px;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-row-label { color: var(--text2); }
        .detail-row-value { font-weight: 600; }
        .detail-row-value.danger { color: var(--red); font-weight: 700; }
        .detail-row-value.success { color: var(--green); }
        .detail-row-value.orange { color: var(--orange); }

        /* ── Section header ── */
        .section-actions {
            display: flex; align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .section-title { font-size: 18px; font-weight: 700; }
        .page-title    { font-size: 18px; font-weight: 700; }

        /* ── Back link ── */
        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            color: var(--text2); font-size: 14px; margin-bottom: 14px;
            font-weight: 500;
        }

        /* ── Empty state ── */
        .empty-state {
            text-align: center; padding: 40px 20px; color: var(--text2);
        }

        /* ── Info row (livreur stats) ── */
        .info-row {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; background: #F9FAFB;
            border-radius: var(--radius-sm); margin-bottom: 8px;
        }
        .info-row-icon { font-size: 18px; }
        .info-row-label { font-size: 13px; color: var(--text2); }
        .info-row-value { font-size: 15px; font-weight: 700; margin-left: auto; }
        .info-row-value.danger  { color: var(--red); }
        .info-row-value.green   { color: var(--green); }
        .info-row-value.orange  { color: var(--orange); }

        /* ── Category icon (dépenses) ── */
        .cat-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }
        .cat-icon.farine  { background: #FFF3E6; }
        .cat-icon.levure  { background: #F0FDF4; }
        .cat-icon.salaire { background: #EFF6FF; }
        .cat-icon.transport { background: #F0FDF4; }
        .cat-icon.entretien { background: #F0FDF4; }
        .cat-icon.divers  { background: #F3F4F6; }

        /* ── Bottom navigation ── */
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: var(--white);
            border-top: 1px solid var(--border);
            box-shadow: 0 -2px 12px rgba(0,0,0,.06);
            z-index: 200;
            padding-bottom: env(safe-area-inset-bottom);
        }
        .nav-wrap {
            display: flex; align-items: stretch; height: var(--nav-h);
        }
        .nav-item {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 4px; cursor: pointer; border: none;
            background: transparent; color: #9CA3AF;
            font-size: 11px; font-weight: 500;
            text-decoration: none; padding: 0;
            transition: color .15s;
        }
        .nav-item:hover, .nav-item.active { color: var(--orange); }
        .nav-item svg { width: 22px; height: 22px; }
        .nav-item.plus-btn .plus-circle {
            width: 42px; height: 42px; background: var(--orange);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        .nav-item.plus-btn .plus-circle svg { color: #fff; width: 22px; height: 22px; }
        .nav-item.plus-btn { color: var(--orange); gap: 2px; }

        /* ── More overlay ── */
        .more-overlay { display:none; position:fixed; inset:0; z-index:300; }
        .more-overlay.open { display:block; }
        .more-backdrop { position:absolute; inset:0; background:rgba(0,0,0,.45); }
        .more-sheet {
            position: absolute; bottom: var(--nav-h); left: 0; right: 0;
            background: var(--white);
            border-radius: 22px 22px 0 0;
            padding: 6px 0 16px;
            max-width: 480px; margin: 0 auto;
        }
        @media (min-width: 768px) { .more-sheet { max-width: 520px; } }
        @media (min-width: 1024px) { .more-sheet { max-width: 560px; } }
        .more-handle {
            width: 36px; height: 4px; background: var(--border);
            border-radius: 2px; margin: 10px auto 16px;
        }
        .more-item {
            display: flex; align-items: center; gap: 14px;
            padding: 14px 20px; font-size: 15px; font-weight: 500;
            color: var(--text); text-decoration: none; transition: background .1s;
            border: none; background: none; width: 100%; cursor: pointer; text-align: left;
        }
        .more-item:hover { background: var(--bg); }
        .more-item svg { width: 22px; height: 22px; color: var(--text2); flex-shrink: 0; }
        .more-section-title {
            font-size: 11px; font-weight: 700; color: var(--text2);
            text-transform: uppercase; letter-spacing: .08em;
            padding: 8px 20px 4px;
        }
        .more-divider { height: 1px; background: var(--border); margin: 4px 0; }

        /* ── Input with icon ── */
        .input-icon-wrap { position: relative; }
        .input-icon-wrap .form-input { padding-left: 42px; }
        .input-icon-wrap .icon-left {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            color: var(--text2); font-size: 16px; pointer-events: none;
        }

        /* ── Helper utilities ── */
        .text-muted  { color: var(--text2); }
        .text-orange { color: var(--orange); }
        .text-red    { color: var(--red); }
        .text-green  { color: var(--green); }
        .fw-700      { font-weight: 700; }
        .fw-800      { font-weight: 800; }
        .fs-13       { font-size: 13px; }
        .fs-12       { font-size: 12px; }
        .mt-8        { margin-top: 8px; }
        .mt-12       { margin-top: 12px; }
        .mb-8        { margin-bottom: 8px; }
        .mb-12       { margin-bottom: 12px; }
        .flex        { display: flex; }
        .flex-col    { display: flex; flex-direction: column; }
        .items-center{ align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-8       { gap: 8px; }
        .gap-12      { gap: 12px; }
        .full-width  { width: 100%; }

        /* ── Activity item ── */
        .activity-item {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 10px 0; border-bottom: 1px solid var(--border);
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-dot {
            width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; margin-top: 1px;
        }
        .activity-dot.bread    { background: var(--orange-bg); }
        .activity-dot.stock    { background: var(--blue-bg); }
        .activity-dot.money    { background: var(--green-bg); }
        .activity-dot.warn     { background: var(--yellow-bg); }
        .activity-label { font-size: 14px; font-weight: 600; }
        .activity-sub   { font-size: 12px; color: var(--text2); margin-top: 1px; }
        .activity-time  { font-size: 11px; color: var(--text2); white-space: nowrap; flex-shrink: 0; }

        /* ── Double action buttons ── */
        .action-buttons {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
            margin-top: 16px;
        }

        /* ── Bilan card ── */
        .bilan-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 9px 0; border-bottom: 1px solid var(--border); font-size: 14px;
        }
        .bilan-row:last-child { border-bottom: none; }
        .bilan-total {
            display: flex; justify-content: space-between; align-items: center;
            padding-top: 12px; font-size: 16px; font-weight: 700;
        }

        /* Alert styles */
        .alert-danger  { background: var(--red-bg);   color: #991B1B; padding: 12px 14px; border-radius: var(--radius-sm); margin-bottom: 12px; font-size: 14px; }
        .alert-success { background: var(--green-bg); color: #065F46; padding: 12px 14px; border-radius: var(--radius-sm); margin-bottom: 12px; font-size: 14px; }
    </style>
</head>
<body>
    {{-- Topbar --}}
    @php
        $nomBoulangerie = auth()->user()?->boulangerie?->nom ?? 'Ma Boulangerie';
        $userName = auth()->user()?->name ?? '';
    @endphp
    <header class="topbar">
        <a href="{{ route('dashboard') }}" class="topbar-left">
            <div class="topbar-icon">🥖</div>
            <div>
                <div class="topbar-boulangerie">{{ $nomBoulangerie }}</div>
                <div class="topbar-user">{{ $userName }}</div>
            </div>
        </a>
        {{-- Bell icon --}}
        <div style="color:var(--text2)">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
    </header>

    {{-- Page content --}}
    <div class="main-wrap">
        @if(session('success'))
            <div class="flash flash-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error">{{ session('error') }}</div>
        @endif
        @if(session('warning'))
            <div class="flash flash-warning">{{ session('warning') }}</div>
        @endif

        @yield('content')
    </div>

    {{-- Bottom navigation --}}
    <nav class="bottom-nav">
        <div class="nav-wrap">
            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Accueil
            </a>

            <a href="{{ route('matieres-premieres.index') }}"
               class="nav-item {{ request()->routeIs('matieres-premieres.*') || request()->routeIs('achats-matieres-premieres.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Stock
            </a>

            <a href="{{ route('productions.index') }}"
               class="nav-item {{ request()->routeIs('productions.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                Production
            </a>

            <a href="{{ route('livreurs.index') }}"
               class="nav-item {{ request()->routeIs('livreurs.*') || request()->routeIs('versements.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Livreurs
            </a>

            <button class="nav-item plus-btn" onclick="openMore()" type="button">
                <div class="plus-circle">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                Plus
            </button>
        </div>
    </nav>

    {{-- More overlay --}}
    <div class="more-overlay" id="moreOverlay">
        <div class="more-backdrop" onclick="closeMore()"></div>
        <div class="more-sheet">
            <div class="more-handle"></div>

            <a href="{{ route('clients-abonnes.index') }}" class="more-item" onclick="closeMore()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Abonnés
            </a>

            <a href="{{ route('depenses.index') }}" class="more-item" onclick="closeMore()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Dépenses
            </a>

            @php $userRole = auth()->user()?->role?->nom; @endphp

            @if($userRole === 'proprietaire')
                <div class="more-divider"></div>

                <a href="{{ route('gerants.index') }}" class="more-item" onclick="closeMore()">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Gestion gérants
                </a>

                <a href="{{ route('bilan.index') }}" class="more-item" onclick="closeMore()">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Bilan financier
                </a>

                <a href="{{ route('statistiques.index') }}" class="more-item" onclick="closeMore()">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 8v8m-4-5v5m-4-2v2M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Statistiques
                </a>

                <a href="{{ route('parametres.edit') }}" class="more-item" onclick="closeMore()">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Paramètres
                </a>
            @endif

            <div class="more-divider"></div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="more-item" style="color:var(--red)">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Déconnexion
                </button>
            </form>
        </div>
    </div>

    <script>
        function openMore()  { document.getElementById('moreOverlay').classList.add('open'); }
        function closeMore() { document.getElementById('moreOverlay').classList.remove('open'); }
        document.querySelector('.more-backdrop')?.addEventListener('click', closeMore);

        // Auto-dismiss success flash after 2 seconds
        (function() {
            var flash = document.querySelector('.flash-success');
            if (flash) {
                setTimeout(function() {
                    flash.style.transition = 'opacity .4s';
                    flash.style.opacity = '0';
                    setTimeout(function() { flash.remove(); }, 400);
                }, 2000);
            }
        })();

        // Menus ··· : position:fixed pour échapper aux overflow:hidden et toujours apparaître au premier plan
        (function() {
            function getDropdown(detailsEl) {
                for (var i = 0; i < detailsEl.children.length; i++) {
                    if (detailsEl.children[i].tagName.toLowerCase() !== 'summary') {
                        return detailsEl.children[i];
                    }
                }
                return null;
            }
            function closeAllDetails(except) {
                document.querySelectorAll('details[open]').forEach(function(d) {
                    if (d !== except) d.removeAttribute('open');
                });
            }
            function positionDropdown(detailsEl) {
                var dropdown = getDropdown(detailsEl);
                if (!dropdown) return;
                // 1. Placer en fixed + invisible → force un flush de layout synchrone
                dropdown.style.position   = 'fixed';
                dropdown.style.visibility = 'hidden';
                dropdown.style.top        = '0';
                dropdown.style.bottom     = 'auto';
                dropdown.style.right      = '0';
                dropdown.style.zIndex     = '9999';
                // 2. Lire offsetHeight déclenche le reflow → hauteur réelle garantie
                var rect  = detailsEl.getBoundingClientRect();
                var menuH = dropdown.offsetHeight || 110;
                // 3. Espace sous le bouton, en retirant la barre de nav (68px + 8px marge)
                var below = window.innerHeight - rect.bottom - 76;
                dropdown.style.right = Math.round(window.innerWidth - rect.right) + 'px';
                if (below < menuH + 4) {
                    // Pas assez de place en bas → s'ouvre vers le haut
                    dropdown.style.top    = 'auto';
                    dropdown.style.bottom = Math.round(window.innerHeight - rect.top) + 'px';
                } else {
                    // Assez de place → s'ouvre vers le bas
                    dropdown.style.top    = Math.round(rect.bottom) + 'px';
                    dropdown.style.bottom = 'auto';
                }
                // 4. Rendre visible une fois positionné
                dropdown.style.visibility = '';
            }
            document.addEventListener('click', function(e) {
                var d = e.target.closest('details');
                closeAllDetails(d);
                if (d && d.open) positionDropdown(d);
            });
            document.addEventListener('scroll', function() {
                closeAllDetails(null);
            }, true);
        })();
    </script>
</body>
</html>
