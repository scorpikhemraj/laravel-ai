<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register Biometric</title>
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
            padding: 52px 44px;
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(245, 158, 11, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            text-align: center;
        }
        .scanner {
            width: 100px; height: 100px;
            margin: 0 auto 28px;
            position: relative;
            display: flex; align-items: center; justify-content: center;
        }
        .scanner-ring {
            position: absolute; inset: 0;
            border-radius: 50%;
        }
        .scanner-ring::before {
            content: '';
            position: absolute; inset: 0;
            border-radius: 50%;
            background: conic-gradient(from 0deg, var(--brand), var(--accent), transparent 60%);
            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 3px), #fff 0);
            mask: radial-gradient(farthest-side, transparent calc(100% - 3px), #fff 0);
            animation: spin 3s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .scanner-inner {
            width: 80px; height: 80px; border-radius: 50%;
            background: linear-gradient(135deg, var(--surface-700), var(--surface-800));
            border: 1px solid var(--border-amber);
            display: flex; align-items: center; justify-content: center;
            position: relative; z-index: 1;
        }
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
        .steps {
            text-align: left;
            background: var(--surface-700);
            border: 1px solid var(--border-amber);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 28px;
        }
        .step-item {
            display: flex; gap: 12px; align-items: flex-start;
            font-size: 13px; color: var(--muted); margin-bottom: 12px;
        }
        .step-item:last-child { margin-bottom: 0; }
        .step-num {
            width: 22px; height: 22px; border-radius: 50%;
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            display: flex; align-items: center; justify-content: center;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px; font-weight: 500; color: var(--brand);
            flex-shrink: 0; margin-top: 1px;
        }
        .btn {
            width: 100%;
            background: var(--brand);
            color: #000000;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-family: 'Figtree', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s;
            margin-bottom: 12px;
        }
        .btn:hover:not(:disabled) { opacity: 0.88; transform: translateY(-1px); }
        .btn:disabled { opacity: 0.4; cursor: not-allowed; }
        #statusMsg {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            color: var(--muted);
            margin-top: 8px;
            min-height: 18px;
            transition: color 0.3s;
        }
        #statusMsg.success { color: var(--success); }
        #statusMsg.error   { color: var(--error); }
    </style>
</head>
<body>
<div class="ambient">
    <div class="orb orb-amber"></div>
    <div class="orb orb-cyan"></div>
</div>

<div class="card">
    <div class="scanner">
        <div class="scanner-ring" id="scanRing"></div>
        <div class="scanner-inner" id="scanIcon">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 11c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2z" fill="#f59e0b"/>
                <path d="M12 11c0-1.1-.9-2-2-2s-2 .9-2 2 .9 2 2 2 2-.9 2-2z" stroke="#f59e0b" stroke-width="1.5"/>
                <path d="M7.5 16.5c-.8-1.2-1.5-2.8-1.5-4.5 0-2.5 2-4.5 4.5-4.5h2c2.5 0 4.5 2 4.5 4.5 0 1.7-.7 3.3-1.5 4.5" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M9 18h6" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M10 20h4" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

    <h1>Register Biometric</h1>
    <p>Set up Face ID or Fingerprint for quick, passwordless login on this device.</p>

    <div class="steps">
        <div class="step-item"><div class="step-num">1</div><span>Click the button below to begin</span></div>
        <div class="step-item"><div class="step-num">2</div><span>Your browser will prompt you to authenticate</span></div>
        <div class="step-item"><div class="step-num">3</div><span>Use Face ID, fingerprint, or security key</span></div>
        <div class="step-item"><div class="step-num">4</div><span>Done! You can log in biometrically next time</span></div>
    </div>

    <button class="btn" id="registerBtn" onclick="registerBiometric()">
        Register Biometric
    </button>

    <div id="statusMsg"></div>
</div>

<script>
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    function setStatus(msg, type = '') {
        const el = document.getElementById('statusMsg');
        el.textContent = msg;
        el.className = type;
    }

    function setIconDefault() {
        document.getElementById('scanIcon').innerHTML = '<svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 11c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2z" fill="#f59e0b"/><path d="M12 11c0-1.1-.9-2-2-2s-2 .9-2 2 .9 2 2 2 2-.9 2-2z" stroke="#f59e0b" stroke-width="1.5"/><path d="M7.5 16.5c-.8-1.2-1.5-2.8-1.5-4.5 0-2.5 2-4.5 4.5-4.5h2c2.5 0 4.5 2 4.5 4.5 0 1.7-.7 3.3-1.5 4.5" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/><path d="M9 18h6" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/><path d="M10 20h4" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/></svg>';
    }

    function setIconLoading() {
        document.getElementById('scanIcon').innerHTML = '<svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-dasharray="31.4" stroke-dashoffset="10"/></svg>';
    }

    function setIconSuccess() {
        document.getElementById('scanIcon').innerHTML = '<svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
    }

    function setIconError() {
        document.getElementById('scanIcon').innerHTML = '<svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
    }

    async function registerBiometric() {
        const btn = document.getElementById('registerBtn');
        btn.disabled = true;
        setStatus('Preparing registration challenge…');
        setIconLoading();

        try {
            const res = await fetch('{{ route('auth.biometric.register.options') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({})
            });
            if (!res.ok) throw new Error('Failed to get registration options');
            const options = await res.json();

            options.challenge = base64UrlDecode(options.challenge);
            options.user.id   = base64UrlDecode(options.user.id);
            if (options.excludeCredentials) {
                options.excludeCredentials = options.excludeCredentials.map(c => ({
                    ...c, id: base64UrlDecode(c.id)
                }));
            }

            setStatus('Waiting for biometric prompt…');
            setIconDefault();

            const credential = await navigator.credentials.create({ publicKey: options });

            setStatus('Saving credential…');
            setIconLoading();

            const saveRes = await fetch('{{ route('auth.biometric.register.submit') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    id:       credential.id,
                    rawId:    arrayToBase64Url(credential.rawId),
                    type:     credential.type,
                    response: {
                        attestationObject: arrayToBase64Url(credential.response.attestationObject),
                        clientDataJSON:    arrayToBase64Url(credential.response.clientDataJSON),
                    }
                })
            });

            const result = await saveRes.json();
            if (!result.success) throw new Error(result.message || 'Registration failed');

            setIconSuccess();
            setStatus('Biometric registered successfully!', 'success');
            btn.textContent = '✓ Registered';

            setTimeout(() => window.location.href = '{{ route('dashboard') }}', 1500);

        } catch (err) {
            setIconError();
            setStatus(err.name === 'NotAllowedError'
                ? 'Prompt dismissed. Click again to retry.'
                : (err.message || 'Registration failed'), 'error');
            btn.disabled = false;
        }
    }

    function base64UrlDecode(s) {
        const pad = '='.repeat((4 - s.length % 4) % 4);
        const b64 = (s + pad).replace(/-/g, '+').replace(/_/g, '/');
        return Uint8Array.from(atob(b64), c => c.charCodeAt(0)).buffer;
    }

    function arrayToBase64Url(buf) {
        return btoa(String.fromCharCode(...new Uint8Array(buf)))
            .replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
    }

    if (!window.PublicKeyCredential) {
        document.getElementById('registerBtn').disabled = true;
        setStatus('Your browser does not support WebAuthn biometrics.', 'error');
    }
</script>
</body>
</html>
