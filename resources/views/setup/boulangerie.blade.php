<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer ma boulangerie — Gestion Boulangerie</title>
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
            margin-bottom: 4px;
        }
        .brand-user {
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
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
        .form-hint { font-size: 11px; color: var(--text2); margin-top: 4px; }
        .btn-primary {
            width: 100%; padding: 14px;
            background: var(--orange); color: #fff;
            border: none; border-radius: 50px;
            font-size: 16px; font-weight: 700; font-family: inherit;
            cursor: pointer; margin-top: 8px;
            transition: opacity .15s;
        }
        .btn-primary:hover { opacity: .88; }
        .logout-link {
            display: block; text-align: center;
            margin-top: 20px; font-size: 12px;
            color: var(--text2); text-decoration: none;
        }
        .logout-link:hover { color: var(--red); }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand-icon">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width:44px;height:44px;object-fit:contain">
        </div>
        <div class="brand-name">Ma Boulangerie</div>
        <div class="brand-sub">Bonjour {{ auth()->user()->name }},</div>
        <div class="brand-user">Configurez votre boulangerie pour commencer</div>

        <form method="POST" action="{{ route('setup.boulangerie.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="nom">Nom de la boulangerie *</label>
                <input class="form-input {{ $errors->has('nom') ? 'is-invalid' : '' }}"
                       type="text" id="nom" name="nom"
                       value="{{ old('nom') }}"
                       placeholder="Boulangerie Al Amine"
                       required autofocus>
                @error('nom')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="telephone">Téléphone *</label>
                <input class="form-input {{ $errors->has('telephone') ? 'is-invalid' : '' }}"
                       type="tel" id="telephone" name="telephone"
                       value="{{ old('telephone') }}"
                       placeholder="77 000 00 00"
                       required>
                @error('telephone')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="adresse">Adresse *</label>
                <input class="form-input {{ $errors->has('adresse') ? 'is-invalid' : '' }}"
                       type="text" id="adresse" name="adresse"
                       value="{{ old('adresse') }}"
                       placeholder="Dakar, Médina"
                       required>
                @error('adresse')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email (optionnel)</label>
                <input class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       type="email" id="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="contact@maboulangerie.sn">
                @error('email')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <button class="btn-primary" type="submit">Créer ma boulangerie</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-link" style="background:none;border:none;cursor:pointer;width:100%">
                Se déconnecter
            </button>
        </form>
    </div>
</body>
</html>
