<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Biometric Login</title>
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

        .bio-icon-wrap {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto 28px;
        }

        .bio-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid transparent;
            animation: spin 3s linear infinite;
        }
        .bio-ring::before {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, var(--brand), var(--accent), transparent 60%);
            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 3px), #fff 0);
            mask: radial-gradient(farthest-side, transparent calc(100% - 3px), #fff 0);
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .bio-inner {
            position: absolute;
            inset: 6px;
            background: linear-gradient(135deg, var(--surface-700), var(--surface-800));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-amber);
        }

        .bio-ring.success::before { background: conic-gradient(from 0deg, var(--success), var(--accent), transparent 60%); }
        .bio-ring.error::before   { background: conic-gradient(from 0deg, var(--error), #f97316, transparent 60%); }
        .bio-ring.idle            { animation: none; }
        .bio-ring.idle::before    { background: conic-gradient(from 0deg, rgba(245,158,11,0.2), transparent 80%); }

        h1 {
            font-family: 'Figtree', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--text-bright);
            margin-bottom: 6px;
        }

        .email {
            color: var(--muted);
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            margin-bottom: 10px;
        }

        #statusText {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 36px;
            min-height: 20px;
            transition: color 0.3s;
        }
        #statusText.success { color: var(--success); }
        #statusText.error   { color: var(--error); }

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

        .btn-outline {
            width: 100%;
            background: transparent;
            border: 1px solid var(--border-amber);
            color: var(--muted);
            border-radius: 12px;
            padding: 14px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            cursor: pointer;
            transition: border-color 0.2s, color 0.2s;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-outline:hover { border-color: var(--brand); color: var(--text); }
        .btn-outline svg { width: 18px; height: 18px; }

        .divider {
            text-align: center;
            color: var(--muted);
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin: 16px 0;
            position: relative;
        }
        .divider::before, .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 38%;
            height: 1px;
            background: var(--border-amber);
        }
        .divider::before { left: 0; }
        .divider::after  { right: 0; }

        .links {
            margin-top: 20px;
            font-size: 12px;
        }
        .links a { color: var(--brand); text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="ambient">
    <div class="orb orb-amber"></div>
    <div class="orb orb-cyan"></div>
</div>

<div class="card">
    <div class="bio-icon-wrap">
        <div class="bio-ring idle" id="bioRing"></div>
        <div class="bio-inner" id="bioIcon">
            <svg width="38" height="38" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 11c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2z" fill="#f59e0b"/>
                <path d="M12 11c0-1.1-.9-2-2-2s-2 .9-2 2 .9 2 2 2 2-.9 2-2z" stroke="#f59e0b" stroke-width="1.5"/>
                <path d="M7.5 16.5c-.8-1.2-1.5-2.8-1.5-4.5 0-2.5 2-4.5 4.5-4.5h2c2.5 0 4.5 2 4.5 4.5 0 1.7-.7 3.3-1.5 4.5" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M9 18h6" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M10 20h4" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

    <h1>Biometric Login</h1>
    <div class="email">{{ $email }}</div>
    <div id="statusText">Ready to authenticate</div>

    <button class="btn" id="bioBtn" onclick="startBiometric()">
        Use Face ID / Fingerprint
    </button>

    <div class="divider">or</div>

    @if(session('auth.has_pin'))
        <button class="btn-outline" onclick="window.location='{{ route('auth.pin') }}'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="7.5" cy="16" r="1" fill="currentColor"/><circle cx="12" cy="16" r="1" fill="currentColor"/><circle cx="16.5" cy="16" r="1" fill="currentColor"/><path d="M7 11V8a5 5 0 0 1 10 0v3"/></svg>
            Use PIN instead
        </button>
    @endif

    <button class="btn-outline" onclick="window.location='{{ route('auth.password') }}'">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Use password instead
    </button>

    <div class="links">
        <a href="{{ route('login') }}">← Different account</a>
    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let isProcessing = false;

    function setStatus(text, type = '') {
        const el = document.getElementById('statusText');
        el.textContent = text;
        el.className = type;
    }

    function setRingState(state) {
        const ring = document.getElementById('bioRing');
        ring.className = 'bio-ring ' + state;
    }

    function setIconSuccess() {
        document.getElementById('bioIcon').innerHTML = '<svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
    }

    function setIconError() {
        document.getElementById('bioIcon').innerHTML = '<svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
    }

    function setIconDefault() {
        document.getElementById('bioIcon').innerHTML = '<svg width="38" height="38" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 11c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2z" fill="#f59e0b"/><path d="M12 11c0-1.1-.9-2-2-2s-2 .9-2 2 .9 2 2 2 2-.9 2-2z" stroke="#f59e0b" stroke-width="1.5"/><path d="M7.5 16.5c-.8-1.2-1.5-2.8-1.5-4.5 0-2.5 2-4.5 4.5-4.5h2c2.5 0 4.5 2 4.5 4.5 0 1.7-.7 3.3-1.5 4.5" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/><path d="M9 18h6" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/><path d="M10 20h4" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round"/></svg>';
    }

    async function startBiometric() {
        if (isProcessing) return;
        isProcessing = true;

        const btn = document.getElementById('bioBtn');
        btn.disabled = true;
        setRingState('');
        setStatus('Requesting biometric challenge…');
        setIconDefault();

        try {
            const optRes = await fetch('{{ route('auth.biometric.options') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({})
            });

            if (!optRes.ok) throw new Error('Failed to get challenge');
            const options = await optRes.json();

            setStatus('Touch your sensor or look at camera…');

            options.challenge = base64UrlDecode(options.challenge);
            if (options.allowCredentials) {
                options.allowCredentials = options.allowCredentials.map(c => ({
                    ...c,
                    id: base64UrlDecode(c.id)
                }));
            }

            const credential = await navigator.credentials.get({ publicKey: options });

            setStatus('Verifying…');
            setRingState('');

            const verifyRes = await fetch('{{ route('auth.biometric.submit') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    id:       credential.id,
                    rawId:    arrayToBase64Url(credential.rawId),
                    type:     credential.type,
                    response: {
                        authenticatorData: arrayToBase64Url(credential.response.authenticatorData),
                        clientDataJSON:    arrayToBase64Url(credential.response.clientDataJSON),
                        signature:         arrayToBase64Url(credential.response.signature),
                        userHandle:        credential.response.userHandle
                            ? arrayToBase64Url(credential.response.userHandle)
                            : null,
                    }
                })
            });

            const result = await verifyRes.json();

            if (result.success) {
                setRingState('success');
                setIconSuccess();
                setStatus('Verified! Redirecting…', 'success');
                setTimeout(() => window.location.href = result.redirect, 600);
            } else {
                throw new Error(result.error || 'Verification failed');
            }

        } catch (err) {
            setRingState('error');
            setIconError();

            if (err.name === 'NotAllowedError') {
                setStatus('Biometric prompt was dismissed.', 'error');
            } else {
                setStatus(err.message || 'Authentication failed. Try again.', 'error');
            }

            setTimeout(() => {
                setRingState('idle');
                setIconDefault();
                setStatus('Ready to authenticate');
                btn.disabled = false;
                isProcessing = false;
            }, 2000);
        }
    }

    function base64UrlDecode(base64url) {
        const padding = '='.repeat((4 - base64url.length % 4) % 4);
        const base64 = (base64url + padding).replace(/-/g, '+').replace(/_/g, '/');
        const binary = window.atob(base64);
        return Uint8Array.from(binary, c => c.charCodeAt(0)).buffer;
    }

    function arrayToBase64Url(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        bytes.forEach(b => binary += String.fromCharCode(b));
        return window.btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
    }

    if (window.PublicKeyCredential) {
        setTimeout(startBiometric, 500);
    } else {
        setStatus('This browser does not support biometric login.', 'error');
        document.getElementById('bioBtn').disabled = true;
    }
</script>

</body>
</html>
