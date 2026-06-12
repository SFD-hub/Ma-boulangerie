<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer un compte — Gestion Boulangerie</title>
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
        * { box-sizing: border-box; margin: 0; padding: 0; }
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
        .brand-icon {
            width: 72px; height: 72px;
            background: var(--orange-bg);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px;
            margin: 0 auto 16px;
        }
        .brand-name {
            text-align: center;
            font-size: 18px; font-weight: 800;
            color: var(--orange);
            text-transform: uppercase; letter-spacing: .04em;
            line-height: 1.3;
            margin-bottom: 6px;
        }
        .brand-sub {
            text-align: center;
            font-size: 13px;
            color: var(--text2);
            margin-bottom: 28px;
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
        .form-input.is-invalid { border-color: var(--red); }
        .field-error { font-size: 12px; color: var(--red); margin-top: 4px; }
        .btn-primary {
            width: 100%; padding: 14px;
            background: var(--orange); color: #fff;
            border: none; border-radius: 50px;
            font-size: 16px; font-weight: 700; font-family: inherit;
            cursor: pointer; margin-top: 8px;
            transition: opacity .15s;
        }
        .btn-primary:hover { opacity: .88; }
        .login-link {
            display: block; text-align: center;
            margin-top: 20px; font-size: 13px;
            color: var(--text2); text-decoration: none;
        }
        .login-link a { color: var(--orange); font-weight: 600; text-decoration: none; }
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
    <div class="card">
        <div class="brand-icon">🥖</div>
        <div class="brand-name">Ma Boulangerie</div>
        <div class="brand-sub">Créez votre compte</div>

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="name">Nom complet *</label>
                <input class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                       type="text" id="name" name="name"
                       value="{{ old('name') }}"
                       placeholder="Prénom et nom"
                       autocomplete="name" required autofocus>
                @error('name')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="telephone">Téléphone *</label>
                <input class="form-input {{ $errors->has('telephone') ? 'is-invalid' : '' }}"
                       type="tel" id="telephone" name="telephone"
                       value="{{ old('telephone') }}"
                       placeholder="77 000 00 00"
                       autocomplete="tel" required>
                @error('telephone')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Mot de passe *</label>
                <div class="password-wrap">
                    <input class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           type="password" id="password" name="password"
                           placeholder="Minimum 8 caractères"
                           autocomplete="new-password" required>
                    <button type="button" class="eye-btn" onclick="togglePwd('password','eye1')">
                        <svg id="eye1" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                @error('password')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirmer le mot de passe *</label>
                <div class="password-wrap">
                    <input class="form-input"
                           type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="Répétez le mot de passe"
                           autocomplete="new-password" required>
                    <button type="button" class="eye-btn" onclick="togglePwd('password_confirmation','eye2')">
                        <svg id="eye2" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button class="btn-primary" type="submit">Créer mon compte</button>
        </form>

        <p class="login-link">Déjà un compte ? <a href="{{ route('login') }}">Se connecter</a></p>
    </div>

    <script>
        function togglePwd(inputId, iconId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
