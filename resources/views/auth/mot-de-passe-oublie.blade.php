<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mot de passe oublié — Ma Boulangerie</title>
    <style>
        :root {
            --orange: #F97316;
            --orange-bg: #FFF7ED;
            --text: #111827;
            --text2: #6B7280;
            --border: #E5E7EB;
            --white: #FFFFFF;
            --green: #10B981;
            --green-bg: #ECFDF5;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            min-height: 100vh;
            background: var(--orange-bg);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .card {
            background: var(--white);
            border-radius: 20px;
            padding: 36px 28px 32px;
            width: 100%; max-width: 380px;
            box-shadow: 0 8px 30px rgba(0,0,0,.08);
        }
        .icon {
            width: 72px; height: 72px;
            background: var(--orange-bg);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px;
            margin: 0 auto 16px;
        }
        h1 {
            text-align: center;
            font-size: 18px; font-weight: 800;
            color: var(--text);
            margin-bottom: 6px;
        }
        .subtitle {
            text-align: center;
            font-size: 13px; color: var(--text2);
            margin-bottom: 24px;
        }
        .info-box {
            background: #F9FAFB;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            font-size: 14px;
            color: var(--text);
            line-height: 1.6;
            text-align: center;
        }
        .contact-row {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 10px;
            font-size: 15px; font-weight: 600;
            text-decoration: none;
            color: var(--text);
            border: 1.5px solid var(--border);
        }
        .contact-row:last-of-type { margin-bottom: 0; }
        .btn-whatsapp {
            background: #25D366;
            color: #fff;
            border-color: #25D366;
        }
        .back-link {
            display: block; text-align: center;
            margin-top: 20px; font-size: 13px;
            color: var(--text2); text-decoration: none;
        }
        .back-link:hover { color: var(--orange); }
    </style>
</head>
<body>
@php
    /* ── Informations de contact support ── */
    /* Modifiez uniquement ces deux lignes pour changer les coordonnées */
    $telephone     = '+221 78 432 28 80';
    $whatsappUrl   = 'https://wa.me/221784322880';
@endphp

<div class="card">
    <div class="icon">🔑</div>
    <h1>Mot de passe oublié</h1>
    <p class="subtitle">Nous allons vous aider à retrouver votre accès.</p>

    <div class="info-box">
        Si vous avez oublié votre mot de passe, veuillez contacter le support.
        Un administrateur pourra réinitialiser votre accès rapidement.
    </div>

    <a href="{{ $whatsappUrl }}" target="_blank" class="contact-row btn-whatsapp">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
        Contacter sur WhatsApp
    </a>

    <div class="contact-row">
        <span>📞</span>
        <span>{{ $telephone }}</span>
    </div>

    <a href="{{ route('login') }}" class="back-link">← Retour à la connexion</a>
</div>
</body>
</html>
