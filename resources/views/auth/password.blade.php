<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Enter Password</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700|figtree:600,700,800|jetbrains-mono:400,500" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --surface-900: #08090c;
            --surface-800: #0c0d12;
            --surface-700: #0f1117;
            --border-amber: rgba(245, 158, 11, 0.12);
            --brand: #f59e0b;
            --accent: #06b6d4;
            --text: #e5e7eb;
            --text-bright: #ffffff;
            --muted: #6b7280;
            --error: #ef4444;
            --success: #10b981;
        }

        body {
            background: var(--surface-900);
            color: var(--text);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .ambient {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }
        .ambient .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
        }
        .ambient .orb-amber {
            width: 500px; height: 500px;
            background: rgba(245, 158, 11, 0.06);
            top: -180px; left: -120px;
        }
        .ambient .orb-cyan {
            width: 400px; height: 400px;
            background: rgba(6, 182, 212, 0.04);
            bottom: -150px; right: -100px;
        }

        .card {
            background: rgba(12, 13, 18, 0.7);
            border: 1px solid var(--border-amber);
            border-radius: 16px;
            padding: 48px 44px;
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(245, 158, 11, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .logo {
            width: 48px; height: 48px;
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 28px;
        }
        .logo svg { width: 22px; height: 22px; }

        h1 {
            font-family: 'Figtree', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--text-bright);
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .subtitle {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 36px;
        }

        .field { margin-bottom: 20px; }

        label.hud-label {
            display: block;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 500;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 8px;
        }

        input[type="password"], input[type="text"] {
            width: 100%;
            background: var(--surface-700);
            border: 1px solid var(--border-amber);
            border-radius: 12px;
            padding: 14px 16px;
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.12);
        }
        input.error { border-color: var(--error); }

        .error-msg {
            color: var(--error);
            font-size: 12px;
            margin-top: 6px;
        }

        .btn {
            width: 100%;
            background: var(--brand);
            color: #000000;
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-family: 'Figtree', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s;
            letter-spacing: 0.02em;
        }
        .btn:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }
        .btn:active { transform: scale(0.99); }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }

        .footer-links {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: var(--muted);
        }
        .footer-links a {
            color: var(--brand);
            text-decoration: none;
        }
        .footer-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="ambient">
    <div class="orb orb-amber"></div>
    <div class="orb orb-cyan"></div>
</div>

<div class="card">
    <div class="logo">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M13 2L4 14h7l-2 8 9-12h-7l2-8z" fill="#f59e0b" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
    <h1>Enter Password</h1>
    <p class="subtitle">{{ $email }}</p>

    @if($errors->any())
        <div class="error-msg" style="margin-bottom:16px; padding:12px; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:12px;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('auth.password.submit') }}">
        @csrf

        <div class="field">
            <label for="password" class="hud-label">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="{{ $errors->has('password') ? 'error' : '' }}"
                required
                autofocus
            >
            @error('password')
                <div class="error-msg">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn">
            Log in →
        </button>
    </form>

    <div class="footer-links">
        <a href="{{ route('login') }}">← Back to email</a>
    </div>
</div>

</body>
</html>
