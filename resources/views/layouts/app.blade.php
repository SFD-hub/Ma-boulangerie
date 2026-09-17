<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Boulangerie')</title>

    {{-- ── PWA ── --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#F97316">

    {{-- iOS --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Ma Boulangerie">
    <link rel="apple-touch-icon" href="/images/icon-192.png">

    {{-- Icône générique --}}
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/images/icon-512.png">

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
            --sidebar-w:   248px;
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
        @media (min-width: 1024px) and (max-width: 1279px) {
            .main-wrap { max-width: 560px; }
            .bottom-nav .nav-wrap { max-width: 560px; }
        }

        /* ── Sidebar (desktop) ── */
        .sidebar { display: none; }

        @media (min-width: 1024px) {
            body { padding-bottom: 0; }

            .sidebar {
                display: flex;
                flex-direction: column;
                position: fixed;
                top: 0; left: 0; bottom: 0;
                width: var(--sidebar-w);
                background: var(--white);
                border-right: 1px solid var(--border);
                z-index: 150;
            }
            .sidebar-brand {
                display: flex; align-items: center; gap: 10px;
                padding: 20px 20px 18px;
                border-bottom: 1px solid var(--border);
                flex-shrink: 0;
            }
            .sidebar-icon {
                width: 38px; height: 38px;
                background: var(--orange-bg); border-radius: 10px;
                display: flex; align-items: center; justify-content: center;
                flex-shrink: 0;
            }
            .sidebar-boulangerie {
                font-size: 13px; font-weight: 700; color: var(--orange);
                line-height: 1.25; text-transform: uppercase; letter-spacing: .02em;
            }
            .sidebar-user { font-size: 12px; color: var(--text2); margin-top: 1px; }
            .sidebar-nav {
                flex: 1; overflow-y: auto;
                padding: 12px 10px;
            }
            .sidebar-section-title {
                font-size: 11px; font-weight: 700; color: var(--text2);
                text-transform: uppercase; letter-spacing: .08em;
                padding: 14px 12px 6px;
            }
            .sidebar-link {
                display: flex; align-items: center; gap: 12px;
                padding: 10px 12px; border-radius: 10px;
                font-size: 14px; font-weight: 600; color: var(--text2);
                margin-bottom: 2px; transition: background .12s, color .12s;
            }
            .sidebar-link svg { width: 20px; height: 20px; flex-shrink: 0; }
            .sidebar-link:hover { background: var(--bg); color: var(--text); }
            .sidebar-link.active { background: var(--orange-bg); color: var(--orange); }
            .sidebar-divider { height: 1px; background: var(--border); margin: 8px 6px; }
            .sidebar-footer {
                padding: 10px; border-top: 1px solid var(--border); flex-shrink: 0;
            }
            .sidebar-footer .sidebar-link { color: var(--red); width: 100%; border: none; background: none; cursor: pointer; text-align: left; font-family: inherit; }

            .topbar { margin-left: var(--sidebar-w); padding: 0 36px; justify-content: flex-end; }
            .topbar-left { display: none; }
            .support-banner { margin-left: var(--sidebar-w); }
            .main-wrap { margin: 0; margin-left: var(--sidebar-w); max-width: 1120px; padding: 32px 36px 48px; }
            .bottom-nav, .more-overlay { display: none; }

            .dashboard-stat-grid { grid-template-columns: repeat(4, 1fr) !important; }
        }
        @media (min-width: 1280px) {
            .main-wrap { max-width: 1280px; }
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

        /* ── Item "boulangerie" du sélecteur (identique mobile/desktop) ── */
        .boulangerie-switch-item {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            width: 100%; padding: 12px 14px; border-radius: 10px;
            font-size: 14px; font-weight: 600; color: var(--text);
            border: none; background: none; cursor: pointer; text-align: left;
            font-family: inherit; text-decoration: none; margin-bottom: 2px;
        }
        .boulangerie-switch-item:hover { background: var(--bg); }
        .boulangerie-switch-item-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .boulangerie-switch-item svg { width: 20px; height: 20px; flex-shrink: 0; color: var(--text2); }
        .boulangerie-switch-item.active {
            background: var(--orange-bg); color: var(--orange);
        }
        .boulangerie-switch-item.active svg { color: var(--orange); }
        .sidebar-nav .boulangerie-switch-item { padding-left: 12px; padding-right: 12px; }
        .more-sheet-body .boulangerie-switch-item { padding: 12px 20px; }

        /* ── More overlay (tiroir latéral gauche) ── */
        .more-overlay {
            position: fixed; inset: 0; z-index: 300;
            visibility: hidden; pointer-events: none;
            transition: visibility 0s linear .32s;
        }
        .more-overlay.open {
            visibility: visible; pointer-events: auto;
            transition-delay: 0s;
        }
        .more-backdrop {
            position: absolute; inset: 0; background: rgba(0,0,0,.45);
            opacity: 0; transition: opacity .32s ease;
        }
        .more-overlay.open .more-backdrop { opacity: 1; }
        .more-sheet {
            position: absolute; top: 0; left: 0; bottom: 0;
            width: min(84%, 340px);
            background: var(--white);
            box-shadow: 6px 0 24px rgba(0,0,0,.16);
            display: flex; flex-direction: column;
            transform: translateX(-100%);
            transition: transform .32s cubic-bezier(.22,1,.36,1);
            padding-bottom: env(safe-area-inset-bottom);
        }
        .more-overlay.open .more-sheet { transform: translateX(0); }
        .more-sheet-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 18px 14px; flex-shrink: 0;
            border-bottom: 1px solid var(--border);
        }
        .more-sheet-title { font-size: 16px; font-weight: 700; color: var(--text); }
        .more-close {
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: none; background: var(--bg); color: var(--text2); cursor: pointer;
        }
        .more-close svg { width: 18px; height: 18px; }
        .more-sheet-body {
            flex: 1; overflow-y: auto; -webkit-overflow-scrolling: touch;
            padding: 6px 0 16px;
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

        /* ── Champ mot de passe avec bouton afficher/masquer ── */
        .password-wrap { position: relative; }
        .password-wrap .form-input { padding-right: 44px; }
        .eye-btn {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--text2); padding: 0; display: flex;
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

        /* ── Cloche de notifications ── */
        .bell-btn { position: relative; background: none; border: none; cursor: pointer; color: var(--text2); padding: 0; display: flex; }
        .notif-badge {
            display: none; position: absolute; top: -5px; right: -6px;
            min-width: 17px; height: 17px; padding: 0 4px; border-radius: 9px;
            background: var(--red); color: #fff; font-size: 10px; font-weight: 700;
            align-items: center; justify-content: center; line-height: 1;
            box-shadow: 0 0 0 2px var(--white);
        }

        /* ── Panneau de notifications ── */
        .notif-overlay {
            position: fixed; inset: 0; z-index: 350;
            visibility: hidden; pointer-events: none;
            transition: visibility 0s linear .25s;
        }
        .notif-overlay.open { visibility: visible; pointer-events: auto; transition-delay: 0s; }
        .notif-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,.35); opacity: 0; transition: opacity .25s ease; }
        .notif-overlay.open .notif-backdrop { opacity: 1; }
        .notif-panel {
            position: absolute; top: 0; right: 0; bottom: 0;
            width: min(88%, 400px);
            background: var(--white);
            box-shadow: -6px 0 24px rgba(0,0,0,.16);
            display: flex; flex-direction: column;
            overflow: hidden;
            transform: translateX(100%);
            transition: transform .32s cubic-bezier(.22,1,.36,1);
            padding-bottom: env(safe-area-inset-bottom);
        }
        .notif-overlay.open .notif-panel { transform: translateX(0); }
        .notif-header {
            display: flex; align-items: flex-start; justify-content: space-between;
            padding: 16px 16px 12px; border-bottom: 1px solid var(--border); flex-shrink: 0;
        }
        .notif-title { font-size: 16px; font-weight: 700; color: var(--text); }
        .notif-subtitle { font-size: 12px; color: var(--orange); font-weight: 600; margin-top: 3px; }
        .notif-tabs {
            display: flex; gap: 6px; padding: 10px 16px;
            overflow-x: auto; flex-shrink: 0; border-bottom: 1px solid var(--border);
        }
        .notif-tabs::-webkit-scrollbar { display: none; }
        .notif-tab {
            flex-shrink: 0; padding: 6px 13px; border-radius: 50px;
            font-size: 12px; font-weight: 600; border: 1.5px solid var(--border);
            background: var(--white); color: var(--text2); cursor: pointer; white-space: nowrap;
            font-family: inherit;
        }
        .notif-tab.active { background: var(--orange); border-color: var(--orange); color: #fff; }
        .notif-toolbar { display: flex; gap: 16px; padding: 10px 16px; flex-shrink: 0; }
        .notif-link-btn {
            background: none; border: none; color: var(--orange); font-size: 12px;
            font-weight: 700; cursor: pointer; padding: 0; font-family: inherit;
        }
        .notif-link-btn:hover { text-decoration: underline; }
        .notif-body { flex: 1; overflow-y: auto; -webkit-overflow-scrolling: touch; }
        .notif-loading, .notif-empty { text-align: center; padding: 44px 16px; color: var(--text2); font-size: 13px; }
        .notif-item { display: flex; gap: 12px; padding: 12px 16px; border-bottom: 1px solid var(--border); }
        .notif-item:last-child { border-bottom: none; }
        .notif-item.read { opacity: .6; }
        .notif-item-icon {
            width: 36px; height: 36px; border-radius: 50%; background: var(--bg);
            display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;
        }
        .notif-item-body { flex: 1; min-width: 0; }
        .notif-item-title { font-size: 13px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 6px; }
        .notif-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--orange); flex-shrink: 0; }
        .notif-item-desc { font-size: 13px; color: var(--text); margin-top: 2px; line-height: 1.4; }
        .notif-item-meta { font-size: 11px; color: var(--text2); margin-top: 4px; }
        .notif-item-actions { display: flex; flex-direction: column; gap: 6px; flex-shrink: 0; }
        .notif-action-btn {
            width: 26px; height: 26px; border-radius: 50%; border: none; background: var(--bg);
            color: var(--text2); display: flex; align-items: center; justify-content: center; cursor: pointer;
        }
        .notif-action-btn:hover { background: var(--border); }
        .notif-read-btn:hover { color: var(--green); }
        .notif-dismiss-btn:hover { color: var(--red); }
        .notif-pagination {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 16px; border-top: 1px solid var(--border); flex-shrink: 0;
        }
        .notif-page-btn {
            background: none; border: 1.5px solid var(--border); border-radius: 8px;
            padding: 6px 11px; font-size: 12px; font-weight: 600; color: var(--text);
            cursor: pointer; font-family: inherit;
        }
        .notif-page-btn:disabled { opacity: .4; cursor: default; }
        .notif-page-info { font-size: 12px; color: var(--text2); }
    </style>
</head>
<body>
    @php
        $nomBoulangerie = auth()->user()?->boulangerie?->nom ?? 'Ma Boulangerie';
        $userName       = auth()->user()?->name ?? '';
        $isSuperAdmin   = auth()->user()?->role?->nom === 'super_admin';
        $userRoleSidebar = auth()->user()?->role?->nom;
        $ownedBoulangeries = $userRoleSidebar === 'proprietaire'
            ? auth()->user()->boulangeries()->orderBy('nom')->get()
            : collect();
        $activeBoulangerieId = auth()->user()?->boulangerie_id;
    @endphp

    {{-- Sidebar (desktop uniquement) --}}
    <aside class="sidebar">
        <a href="{{ $isSuperAdmin ? route('super-admin.dashboard') : route('dashboard') }}" class="sidebar-brand">
            <div class="sidebar-icon">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width:24px;height:24px;object-fit:contain">
            </div>
            <div>
                <div class="sidebar-boulangerie">{{ $nomBoulangerie }}</div>
                <div class="sidebar-user">{{ $userName }}</div>
            </div>
        </a>

        <nav class="sidebar-nav">
            @if($isSuperAdmin)
                <a href="{{ route('super-admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('super-admin.boulangeries') }}" class="sidebar-link {{ request()->routeIs('super-admin.boulangeries*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Boulangeries
                </a>
                <a href="{{ route('super-admin.utilisateurs') }}" class="sidebar-link {{ request()->routeIs('super-admin.utilisateurs') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Utilisateurs
                </a>

                <div class="sidebar-divider"></div>
                <div class="sidebar-section-title">Système</div>

                <a href="{{ route('super-admin.backups') }}" class="sidebar-link {{ request()->routeIs('super-admin.backups*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                    Sauvegardes
                </a>
                <a href="{{ route('super-admin.activity-logs') }}" class="sidebar-link {{ request()->routeIs('super-admin.activity-logs') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    Journal d'activité
                </a>
                <a href="{{ route('a-propos.index') }}" class="sidebar-link {{ request()->routeIs('a-propos.index') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    À propos
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Accueil
                </a>
                <a href="{{ route('matieres-premieres.index') }}" class="sidebar-link {{ request()->routeIs('matieres-premieres.*') || request()->routeIs('achats-matieres-premieres.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Stock
                </a>
                <a href="{{ route('productions.index') }}" class="sidebar-link {{ request()->routeIs('productions.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    Production
                </a>
                <a href="{{ route('livreurs.index') }}" class="sidebar-link {{ request()->routeIs('livreurs.*') || request()->routeIs('versements.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Livreurs
                </a>
                <a href="{{ route('clients-abonnes.index') }}" class="sidebar-link {{ request()->routeIs('clients-abonnes.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Abonnés
                </a>
                <a href="{{ route('depenses.index') }}" class="sidebar-link {{ request()->routeIs('depenses.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Dépenses
                </a>
                <a href="{{ route('produits.index') }}" class="sidebar-link {{ request()->routeIs('produits.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    Produits
                </a>

                @if($userRoleSidebar === 'proprietaire')
                    <div class="sidebar-divider"></div>
                    <div class="sidebar-section-title">Propriétaire</div>

                    <a href="{{ route('gerants.index') }}" class="sidebar-link {{ request()->routeIs('gerants.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Gestion gérants
                    </a>
                    <a href="{{ route('bilan.index') }}" class="sidebar-link {{ request()->routeIs('bilan.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Bilan financier
                    </a>
                    <a href="{{ route('statistiques.index') }}" class="sidebar-link {{ request()->routeIs('statistiques.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Statistiques
                    </a>
                    <a href="{{ route('parametres.edit') }}" class="sidebar-link {{ request()->routeIs('parametres.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Paramètres
                    </a>
                    @if($ownedBoulangeries->count() > 1)
                        <div class="sidebar-divider"></div>
                        <div class="sidebar-section-title">Mes boulangeries</div>
                        @foreach($ownedBoulangeries as $b)
                            @include('partials.boulangerie-switch-item', ['b' => $b, 'activeBoulangerieId' => $activeBoulangerieId])
                        @endforeach
                    @endif

                    <a href="{{ route('boulangeries.ajouter') }}" class="sidebar-link {{ request()->routeIs('boulangeries.ajouter') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Ajouter une boulangerie
                    </a>
                @endif

                <div class="sidebar-divider"></div>
                @unless($userRoleSidebar === 'proprietaire')
                    {{-- Pour le propriétaire, "Mon compte" est intégré dans Paramètres --}}
                    <a href="{{ route('profil.edit') }}" class="sidebar-link {{ request()->routeIs('profil.edit') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Mon compte
                    </a>
                @endunless
                <a href="{{ route('a-propos.index') }}" class="sidebar-link {{ request()->routeIs('a-propos.index') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    À propos
                </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    {{-- Topbar --}}
    <header class="topbar">
        <a href="{{ route('dashboard') }}" class="topbar-left">
            <div class="topbar-icon">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width:28px;height:28px;object-fit:contain">
            </div>
            <div>
                <div class="topbar-boulangerie">{{ $nomBoulangerie }}</div>
                <div class="topbar-user">{{ $userName }}</div>
            </div>
        </a>
        {{-- Cloche de notifications --}}
        @if($isSuperAdmin)
            <div style="color:var(--text2)">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
        @else
            <button type="button" class="bell-btn" onclick="openNotif()">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="notif-badge">0</span>
            </button>
        @endif
    </header>

    {{-- Panneau de notifications --}}
    @unless($isSuperAdmin)
    <div class="notif-overlay" id="notifOverlay">
        <div class="notif-backdrop" onclick="closeNotif()"></div>
        <div class="notif-panel">
            <div class="notif-header">
                <div>
                    <div class="notif-title">Activités</div>
                    <div class="notif-subtitle" id="notifSubtitle">Chargement…</div>
                </div>
                <button type="button" class="more-close" onclick="closeNotif()">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="notif-tabs" id="notifTabs">
                <button type="button" class="notif-tab active" data-cat="toutes" onclick="notifSetCategorie('toutes')">Toutes</button>
                <button type="button" class="notif-tab" data-cat="vente" onclick="notifSetCategorie('vente')">Ventes</button>
                <button type="button" class="notif-tab" data-cat="achat" onclick="notifSetCategorie('achat')">Achats</button>
                <button type="button" class="notif-tab" data-cat="depense" onclick="notifSetCategorie('depense')">Dépenses</button>
                <button type="button" class="notif-tab" data-cat="gerant" onclick="notifSetCategorie('gerant')">Gérants</button>
            </div>
            <div class="notif-toolbar">
                <button type="button" class="notif-link-btn" onclick="notifToutLire()">Tout lire</button>
                <button type="button" class="notif-link-btn" onclick="notifViderLues()">Vider lues</button>
            </div>
            <div class="notif-body" id="notifBody">
                <div class="notif-loading">Chargement…</div>
            </div>
        </div>
    </div>
    @endunless

    {{-- Bannière mode support (impersonation) --}}
    @if(session('impersonator_id'))
    <div class="support-banner" style="background:#1D4ED8;color:#fff;padding:10px 16px;text-align:center;
                position:sticky;top:var(--top-h);z-index:99;
                box-shadow:0 2px 8px rgba(0,0,0,.20)">
        <div style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;opacity:.85">
            🔑 MODE SUPPORT ACTIVÉ
        </div>
        <div style="font-size:13px;font-weight:600;margin-top:2px">
            Vous consultez : <strong>{{ auth()->user()?->boulangerie?->nom ?? '…' }}</strong>
        </div>
        <form method="POST" action="{{ route('super-admin.quitter') }}" style="margin-top:8px">
            @csrf
            <button type="submit"
                    style="background:#fff;color:#1D4ED8;border:none;padding:6px 18px;
                           border-radius:20px;font-size:12px;font-weight:700;cursor:pointer">
                ← Retour Super Admin
            </button>
        </form>
    </div>
    @endif

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

    @if($isSuperAdmin)
    {{-- ══ Navigation Super Admin ══ --}}
    <nav class="bottom-nav">
        <div class="nav-wrap">
            <a href="{{ route('super-admin.dashboard') }}"
               class="nav-item {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('super-admin.boulangeries') }}"
               class="nav-item {{ request()->routeIs('super-admin.boulangeries*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Boulangeries
            </a>

            <a href="{{ route('super-admin.utilisateurs') }}"
               class="nav-item {{ request()->routeIs('super-admin.utilisateurs') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Utilisateurs
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

    {{-- More overlay Super Admin --}}
    <div class="more-overlay" id="moreOverlay">
        <div class="more-backdrop" onclick="closeMore()"></div>
        <div class="more-sheet">
            <div class="more-sheet-header">
                <span class="more-sheet-title">Menu</span>
                <button type="button" class="more-close" onclick="closeMore()">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="more-sheet-body">

            <a href="{{ route('super-admin.backups') }}" class="more-item" onclick="closeMore()"
               style="{{ request()->routeIs('super-admin.backups*') ? 'color:var(--orange)' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                </svg>
                Sauvegardes
            </a>

            <a href="{{ route('super-admin.activity-logs') }}" class="more-item" onclick="closeMore()"
               style="{{ request()->routeIs('super-admin.activity-logs') ? 'color:var(--orange)' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Journal d'activité
            </a>

            <a href="{{ route('a-propos.index') }}" class="more-item" onclick="closeMore()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                À propos
            </a>

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
    </div>

    @else
    {{-- ══ Navigation normale (propriétaire / gérant) ══ --}}
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

    {{-- More overlay normal --}}
    <div class="more-overlay" id="moreOverlay">
        <div class="more-backdrop" onclick="closeMore()"></div>
        <div class="more-sheet">
            <div class="more-sheet-header">
                <span class="more-sheet-title">Menu</span>
                <button type="button" class="more-close" onclick="closeMore()">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="more-sheet-body">

            <a href="{{ route('matieres-premieres.index') }}" class="more-item" onclick="closeMore()"
               style="{{ request()->routeIs('matieres-premieres.*') || request()->routeIs('achats-matieres-premieres.*') ? 'color:var(--orange)' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Stock
            </a>

            <a href="{{ route('produits.index') }}" class="more-item" onclick="closeMore()"
               style="{{ request()->routeIs('produits.*') ? 'color:var(--orange)' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Produits
            </a>

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

                @if($ownedBoulangeries->count() > 1)
                    <div class="more-divider"></div>
                    <div class="more-section-title">Mes boulangeries</div>
                    @foreach($ownedBoulangeries as $b)
                        @include('partials.boulangerie-switch-item', ['b' => $b, 'activeBoulangerieId' => $activeBoulangerieId])
                    @endforeach
                @endif

                <a href="{{ route('boulangeries.ajouter') }}" class="more-item" onclick="closeMore()">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter une boulangerie
                </a>
            @endif

            @unless($userRole === 'proprietaire')
                {{-- Pour le propriétaire, "Mon compte" est intégré dans Paramètres --}}
                <a href="{{ route('profil.edit') }}" class="more-item" onclick="closeMore()"
                   style="{{ request()->routeIs('profil.edit') ? 'color:var(--orange)' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Mon compte
                </a>
            @endunless

            <a href="{{ route('a-propos.index') }}" class="more-item" onclick="closeMore()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                À propos
            </a>

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
    </div>
    @endif

    <script>
        function openMore()  { document.getElementById('moreOverlay').classList.add('open'); }
        function closeMore() { document.getElementById('moreOverlay').classList.remove('open'); }
        document.querySelector('.more-backdrop')?.addEventListener('click', closeMore);

        // Afficher / masquer un champ mot de passe
        function togglePwd(inputId) {
            var input = document.getElementById(inputId);
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        // ── Notifications (absent pour le super admin) ──
        if (document.getElementById('notifOverlay')) (function() {
            var notifBaseUrl = "{{ url('/notifications') }}";
            var notifPanelUrl = "{{ route('notifications.panel') }}";
            var notifCountUrl = "{{ route('notifications.count') }}";
            var state = { categorie: 'toutes', page: 1 };

            function csrf() {
                var meta = document.querySelector('meta[name="csrf-token"]');
                return meta ? meta.getAttribute('content') : '';
            }

            function updateBadge(count) {
                document.querySelectorAll('.notif-badge').forEach(function(b) {
                    if (count > 0) {
                        b.textContent = count > 99 ? '99+' : count;
                        b.style.display = 'flex';
                    } else {
                        b.style.display = 'none';
                    }
                });
                var subtitle = document.getElementById('notifSubtitle');
                if (subtitle) {
                    subtitle.textContent = count > 0 ? count + ' non lue' + (count > 1 ? 's' : '') : 'Tout est lu';
                }
            }

            function load() {
                var body = document.getElementById('notifBody');
                if (!body) return;
                body.innerHTML = '<div class="notif-loading">Chargement…</div>';
                var url = notifPanelUrl + '?categorie=' + encodeURIComponent(state.categorie) + '&page=' + state.page;
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        body.innerHTML = data.html;
                        updateBadge(data.unreadCount);
                    })
                    .catch(function() {
                        body.innerHTML = '<div class="notif-empty">Erreur de chargement.</div>';
                    });
            }

            function post(url) {
                return fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
                }).then(function(r) { return r.json(); });
            }

            window.openNotif = function() {
                var overlay = document.getElementById('notifOverlay');
                if (!overlay) return;
                overlay.classList.add('open');
                state.page = 1;
                load();
            };
            window.closeNotif = function() {
                var overlay = document.getElementById('notifOverlay');
                if (overlay) overlay.classList.remove('open');
            };
            window.notifSetCategorie = function(cat) {
                state.categorie = cat;
                state.page = 1;
                document.querySelectorAll('.notif-tab').forEach(function(t) {
                    t.classList.toggle('active', t.dataset.cat === cat);
                });
                load();
            };
            window.notifLoadPage = function(page) {
                state.page = page;
                load();
                document.getElementById('notifBody').scrollTop = 0;
            };
            window.notifMarkRead = function(id) {
                post(notifBaseUrl + '/' + id + '/lire').then(function(data) {
                    updateBadge(data.unreadCount);
                    load();
                });
            };
            window.notifDismiss = function(id) {
                post(notifBaseUrl + '/' + id + '/masquer').then(function(data) {
                    updateBadge(data.unreadCount);
                    load();
                });
            };
            window.notifToutLire = function() {
                post(notifBaseUrl + '/tout-lire').then(function(data) {
                    updateBadge(data.unreadCount);
                    load();
                });
            };
            window.notifViderLues = function() {
                post(notifBaseUrl + '/vider-lues').then(function() {
                    load();
                });
            };

            // Badge initial au chargement de chaque page
            fetch(notifCountUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(data) { updateBadge(data.count); })
                .catch(function() {});
        })();

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

        // Empêche la molette de la souris de modifier un champ numérique
        // (input[type=number]) quand il est focus — comportement natif du
        // navigateur qui change silencieusement la valeur en scrollant la page.
        document.addEventListener('wheel', function (e) {
            if (document.activeElement && document.activeElement.type === 'number') {
                document.activeElement.blur();
            }
        }, { passive: true });

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
                // Ne concerne que les menus "···" (leur contenu est déjà en
                // position:absolute dans le balisage) -- pas les <details>
                // "accordéon" ordinaires (ex: liste des paiements d'une
                // facture), qui doivent rester dans le flux normal de la page.
                if (getComputedStyle(dropdown).position !== 'absolute') return;
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
            });
            // Le clic qui ouvre un <details> bascule son attribut "open" en
            // action par defaut du navigateur, APRES que ce gestionnaire de
            // clic (au-dessus) se soit deja execute -- d.open y est encore a
            // son ancienne valeur (fermee) au moment du clic d'ouverture.
            // Positionner le menu devait donc se faire sur l'evenement
            // "toggle" (qui ne bulle pas, d'ou capture=true), le seul a
            // refleter l'etat reellement a jour -- sinon le menu gardait sa
            // derniere position connue (ou la position par defaut, en haut a
            // gauche) au premier clic, ce qui le faisait deborder en bas
            // d'ecran sur la derniere carte d'une liste.
            document.addEventListener('toggle', function(e) {
                if (e.target.tagName && e.target.tagName.toLowerCase() === 'details' && e.target.open) {
                    positionDropdown(e.target);
                }
            }, true);
            document.addEventListener('scroll', function() {
                closeAllDetails(null);
            }, true);
        })();
    </script>

    {{-- ── Service Worker PWA ── --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .catch(function(err) { console.warn('SW:', err); });
            });
        }
    </script>
</body>
</html>
