<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Enter PIN</title>
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
            padding: 48px 36px;
            width: 100%;
            max-width: 360px;
            position: relative;
            z-index: 1;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(245, 158, 11, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            text-align: center;
        }

        .avatar {
            width: 64px; height: 64px;
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.25);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        }
        .avatar svg { width: 28px; height: 28px; }

        h1 {
            font-family: 'Figtree', sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--text-bright);
            margin-bottom: 4px;
        }

        .email {
            color: var(--muted);
            font-size: 13px;
            font-family: 'JetBrains Mono', monospace;
            margin-bottom: 36px;
        }

        .pin-dots {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-bottom: 32px;
        }

        .dot {
            width: 16px; height: 16px;
            border-radius: 50%;
            border: 2px solid rgba(245, 158, 11, 0.2);
            background: transparent;
            transition: all 0.15s ease;
        }
        .dot.filled {
            background: var(--brand);
            border-color: var(--brand);
            transform: scale(1.1);
        }
        .dot.error {
            background: var(--error);
            border-color: var(--error);
            animation: shake 0.4s ease;
        }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%      { transform: translateX(-4px); }
            60%      { transform: translateX(4px); }
        }

        .numpad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .key {
            background: var(--surface-700);
            border: 1px solid var(--border-amber);
            border-radius: 12px;
            padding: 18px 0;
            font-family: 'Figtree', sans-serif;
            font-size: 20px;
            font-weight: 600;
            color: var(--text-bright);
            cursor: pointer;
            transition: background 0.15s, transform 0.1s, border-color 0.15s;
            user-select: none;
        }
        .key:hover { background: rgba(245, 158, 11, 0.08); border-color: rgba(245, 158, 11, 0.3); }
        .key:active { transform: scale(0.94); background: rgba(245, 158, 11, 0.12); }

        .key.delete { font-size: 18px; color: var(--muted); }
        .key.empty  { cursor: default; background: transparent; border: none; }

        .error-msg {
            color: var(--error);
            font-size: 12px;
            margin-bottom: 16px;
            min-height: 18px;
        }

        .links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 12px;
        }
        .links a {
            color: var(--muted);
            text-decoration: none;
            font-family: 'JetBrains Mono', monospace;
        }
        .links a:hover { color: var(--brand); }
    </style>
</head>
<body>

<div class="ambient">
    <div class="orb orb-amber"></div>
    <div class="orb orb-cyan"></div>
</div>

<div class="card">
    <div class="avatar">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="3" y="11" width="18" height="10" rx="2" stroke="#f59e0b" stroke-width="1.5"/>
            <circle cx="7.5" cy="16" r="1.5" fill="#f59e0b"/>
            <circle cx="12" cy="16" r="1.5" fill="#f59e0b"/>
            <circle cx="16.5" cy="16" r="1.5" fill="#f59e0b"/>
            <path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
    </div>
    <h1>Enter your PIN</h1>
    <div class="email">{{ $email }}</div>

    <div class="pin-dots" id="pinDots">
        @for($i = 0; $i < 6; $i++)
            <div class="dot" id="dot-{{ $i }}"></div>
        @endfor
    </div>

    <div class="error-msg" id="errorMsg">
        @error('pin') {{ $message }} @enderror
    </div>

    <form method="POST" action="{{ route('auth.pin.submit') }}" id="pinForm">
        @csrf
        <input type="hidden" name="pin" id="pinInput">

        <div class="numpad">
            @foreach(range(1, 9) as $n)
                <button type="button" class="key" data-digit="{{ $n }}">{{ $n }}</button>
            @endforeach
            <button type="button" class="key empty"></button>
            <button type="button" class="key" data-digit="0">0</button>
            <button type="button" class="key delete" id="deleteBtn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/><line x1="18" y1="9" x2="12" y2="15"/><line x1="12" y1="9" x2="18" y2="15"/></svg>
            </button>
        </div>
    </form>

    <div class="links">
        <a href="{{ route('login') }}">← Different account</a>
        <a href="{{ route('auth.password') }}">Use password instead</a>
    </div>
</div>

<script>
    let pin = '';
    const MAX = 6;

    function updateDots() {
        for (let i = 0; i < MAX; i++) {
            const dot = document.getElementById(`dot-${i}`);
            dot.classList.toggle('filled', i < pin.length);
            dot.classList.remove('error');
        }
    }

    function submitPin() {
        document.getElementById('pinInput').value = pin;
        document.getElementById('pinForm').submit();
    }

    function shakeError(message) {
        document.getElementById('errorMsg').textContent = message;
        for (let i = 0; i < MAX; i++) {
            document.getElementById(`dot-${i}`).classList.add('error');
        }
        setTimeout(() => { pin = ''; updateDots(); }, 600);
    }

    document.querySelectorAll('.key[data-digit]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (pin.length >= MAX) return;
            pin += btn.dataset.digit;
            updateDots();
            if (pin.length === MAX) {
                setTimeout(submitPin, 120);
            }
        });
    });

    document.getElementById('deleteBtn').addEventListener('click', () => {
        pin = pin.slice(0, -1);
        updateDots();
        document.getElementById('errorMsg').textContent = '';
    });

    document.addEventListener('keydown', (e) => {
        if (/^\d$/.test(e.key) && pin.length < MAX) {
            pin += e.key;
            updateDots();
            if (pin.length === MAX) setTimeout(submitPin, 120);
        } else if (e.key === 'Backspace') {
            pin = pin.slice(0, -1);
            updateDots();
        }
    });

    @error('pin')
        shakeError("{{ $message }}");
    @enderror
</script>

</body>
</html>
