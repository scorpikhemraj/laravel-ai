<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up PIN</title>
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
            padding: 48px 40px;
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(245, 158, 11, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            text-align: center;
        }
        .icon {
            width: 56px; height: 56px;
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        }
        .icon svg { width: 26px; height: 26px; }
        h1 {
            font-family: 'Figtree', sans-serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--text-bright);
            margin-bottom: 8px;
        }
        p  {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .step-label {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
            margin-bottom: 14px;
        }
        .pin-dots {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 28px;
        }
        .dot {
            width: 14px; height: 14px;
            border-radius: 50%;
            border: 2px solid rgba(245, 158, 11, 0.2);
            background: transparent;
            transition: all 0.15s ease;
        }
        .dot.filled {
            background: var(--brand);
            border-color: var(--brand);
            transform: scale(1.15);
        }
        .dot.confirm-filled {
            background: var(--success);
            border-color: var(--success);
            transform: scale(1.15);
        }
        .numpad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 24px;
        }
        .key {
            background: var(--surface-700);
            border: 1px solid var(--border-amber);
            border-radius: 12px;
            padding: 16px 0;
            font-family: 'Figtree', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: var(--text-bright);
            cursor: pointer;
            transition: background 0.15s, transform 0.1s, border-color 0.15s;
            user-select: none;
        }
        .key:hover { background: rgba(245, 158, 11, 0.08); border-color: rgba(245, 158, 11, 0.3); }
        .key:active { transform: scale(0.93); }
        .key.empty { cursor: default; background: transparent; border: none; }
        .key.del { font-size: 16px; color: var(--muted); }
        .error-msg {
            color: var(--error);
            font-size: 13px;
            min-height: 18px;
            margin-bottom: 12px;
        }
        .skip-link {
            color: var(--muted);
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            text-decoration: none;
            display: block;
            margin-top: 8px;
        }
        .skip-link:hover { color: var(--brand); }
    </style>
</head>
<body>
<div class="ambient">
    <div class="orb orb-amber"></div>
    <div class="orb orb-cyan"></div>
</div>

<div class="card">
    <div class="icon" id="stepIcon">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="3" y="11" width="18" height="10" rx="2" stroke="#f59e0b" stroke-width="1.5"/>
            <circle cx="7.5" cy="16" r="1.5" fill="#f59e0b"/>
            <circle cx="12" cy="16" r="1.5" fill="#f59e0b"/>
            <circle cx="16.5" cy="16" r="1.5" fill="#f59e0b"/>
            <path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
    </div>
    <h1 id="stepTitle">Set up a PIN</h1>
    <p id="stepDesc">Choose a 4–6 digit PIN for quick, secure login on this device.</p>

    <div class="step-label" id="stepLabel">Enter new PIN</div>
    <div class="pin-dots" id="pinDots">
        @for($i = 0; $i < 6; $i++)
            <div class="dot" id="dot-{{ $i }}"></div>
        @endfor
    </div>

    <div class="error-msg" id="errorMsg"></div>

    <form method="POST" action="{{ route('auth.setup.submit') }}" id="pinForm">
        @csrf
        <input type="hidden" name="pin" id="pinInput">
        <input type="hidden" name="pin_confirmation" id="pinConfirmInput">

        <div class="numpad">
            @foreach(range(1, 9) as $n)
                <button type="button" class="key" data-digit="{{ $n }}">{{ $n }}</button>
            @endforeach
            <button type="button" class="key empty"></button>
            <button type="button" class="key" data-digit="0">0</button>
            <button type="button" class="key del" id="deleteBtn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/><line x1="18" y1="9" x2="12" y2="15"/><line x1="12" y1="9" x2="18" y2="15"/></svg>
            </button>
        </div>
    </form>

    <a href="{{ route('dashboard') }}" class="skip-link">Skip for now →</a>
</div>

<script>
    let pin = '', confirmPin = '', step = 'enter';
    const MAX = 6, MIN = 4;

    function setIconDefault() {
        document.getElementById('stepIcon').innerHTML = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="11" width="18" height="10" rx="2" stroke="#f59e0b" stroke-width="1.5"/><circle cx="7.5" cy="16" r="1.5" fill="#f59e0b"/><circle cx="12" cy="16" r="1.5" fill="#f59e0b"/><circle cx="16.5" cy="16" r="1.5" fill="#f59e0b"/><path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/></svg>';
    }

    function setIconConfirm() {
        document.getElementById('stepIcon').innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
    }

    function updateDots(type) {
        const currentPin = step === 'enter' ? pin : confirmPin;
        for (let i = 0; i < MAX; i++) {
            const dot = document.getElementById(`dot-${i}`);
            dot.classList.remove('filled', 'confirm-filled');
            if (i < currentPin.length) {
                dot.classList.add(step === 'confirm' ? 'confirm-filled' : 'filled');
            }
        }
    }

    function switchToConfirm() {
        step = 'confirm';
        setIconConfirm();
        document.getElementById('stepTitle').textContent = 'Confirm your PIN';
        document.getElementById('stepDesc').textContent = 'Enter the same PIN again to confirm.';
        document.getElementById('stepLabel').textContent = 'Confirm PIN';
        updateDots();
    }

    function submitSetup() {
        if (pin !== confirmPin) {
            document.getElementById('errorMsg').textContent = 'PINs do not match. Starting over.';
            setTimeout(() => {
                pin = ''; confirmPin = ''; step = 'enter';
                setIconDefault();
                document.getElementById('stepTitle').textContent = 'Set up a PIN';
                document.getElementById('stepLabel').textContent = 'Enter new PIN';
                document.getElementById('errorMsg').textContent = '';
                updateDots();
            }, 1200);
            return;
        }
        document.getElementById('pinInput').value = pin;
        document.getElementById('pinConfirmInput').value = confirmPin;
        document.getElementById('pinForm').submit();
    }

    document.querySelectorAll('.key[data-digit]').forEach(btn => {
        btn.addEventListener('click', () => {
            const current = step === 'enter' ? pin : confirmPin;
            if (current.length >= MAX) return;

            if (step === 'enter') pin += btn.dataset.digit;
            else confirmPin += btn.dataset.digit;

            updateDots();

            const len = step === 'enter' ? pin.length : confirmPin.length;
            if (len >= MIN) {
                if (step === 'enter' && len >= MAX) setTimeout(switchToConfirm, 150);
                else if (step === 'confirm' && len >= pin.length) setTimeout(submitSetup, 120);
            }
        });
    });

    document.getElementById('deleteBtn').addEventListener('click', () => {
        if (step === 'enter') pin = pin.slice(0, -1);
        else confirmPin = confirmPin.slice(0, -1);
        document.getElementById('errorMsg').textContent = '';
        updateDots();
    });

    document.addEventListener('keydown', (e) => {
        if (/^\d$/.test(e.key)) {
            document.querySelector(`.key[data-digit="${e.key}"]`)?.click();
        } else if (e.key === 'Backspace') {
            document.getElementById('deleteBtn').click();
        }
    });
</script>
</body>
</html>
