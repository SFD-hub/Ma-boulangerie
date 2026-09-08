<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Connexion — Ma Boulangerie</title>

    {{-- ── PWA ── --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#F97316">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Ma Boulangerie">
    <link rel="apple-touch-icon" href="/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icon-192.png">

    <style>
        :root {
            --orange: #F97316;
            --orange-bg: #FFF7ED;
            --text: #111827;
            --text2: #6B7280;
            --border: #E5E7EB;
            --white: #FFFFFF;
            --red: #EF4444;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            min-height: 100vh;
            background: var(--orange-bg);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: var(--white);
            border-radius: 20px;
            padding: 36px 28px 32px;
            width: 100%; max-width: 380px;
            box-shadow: 0 8px 30px rgba(0,0,0,.08);
        }
        .brand-block {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 24px;
        }
        .brand-block img {
            display: block;
            width: 130px;
            height: 130px;
            object-fit: contain;
            /* Compense l'espace transparent en bas du PNG */
            margin-bottom: -30px;
        }
        .brand-name {
            text-align: center;
            font-size: 18px; font-weight: 800;
            color: var(--orange);
            text-transform: uppercase; letter-spacing: .04em;
            line-height: 1.3;
        }
        .form-group { margin-bottom: 16px; }
        .form-label {
            display: block; font-size: 13px; font-weight: 600;
            color: var(--text); margin-bottom: 6px;
        }
        .form-input {
            width: 100%; padding: 13px 14px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 15px; font-family: inherit; color: var(--text);
            background: #FAFAFA; transition: border-color .15s;
        }
        .form-input:focus { outline: none; border-color: var(--orange); background: var(--white); }
        .btn-login {
            width: 100%; padding: 14px;
            background: var(--orange); color: #fff;
            border: none; border-radius: 50px;
            font-size: 16px; font-weight: 700; font-family: inherit;
            cursor: pointer; margin-top: 8px;
            transition: opacity .15s;
        }
        .btn-login:hover { opacity: .88; }
        .error-box {
            background: #FEF2F2; border: 1px solid #FCA5A5;
            border-radius: 8px; padding: 10px 14px;
            color: #991B1B; font-size: 13px; margin-bottom: 16px;
        }
        .forgot {
            display: block; text-align: center;
            margin-top: 18px; font-size: 13px;
            color: var(--text2); text-decoration: none;
        }
        .forgot:hover { color: var(--orange); }
        .password-wrap { position: relative; }
        .password-wrap .form-input { padding-right: 44px; }
        .eye-btn {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--text2); padding: 0;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-block">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
            <div class="brand-name">Ma Boulangerie</div>
        </div>

        @if($errors->any())
            <div class="error-box">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="login">Téléphone ou email</label>
                <input class="form-input" type="text" id="login" name="login"
                       value="{{ old('login') }}"
                       placeholder="Entrez votre numéro ou email"
                       autocomplete="username" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <div class="password-wrap">
                    <input class="form-input" type="password" id="password" name="password"
                           placeholder="Entrez votre mot de passe"
                           autocomplete="current-password" required>
                    <button type="button" class="eye-btn" onclick="togglePassword()">
                        <svg id="eye-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button class="btn-login" type="submit">Se connecter</button>
        </form>

        <a href="{{ route('password.info') }}" class="forgot">Mot de passe oublié ?</a>
        <a href="{{ route('register') }}" class="forgot" style="color:#F97316;font-weight:600;margin-top:6px">Créer un compte</a>
    </div>
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .catch(function(err) { console.warn('SW:', err); });
            });
        }
    </script>
</body>
</html>
