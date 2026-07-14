<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the initial login form.
     * Step 1: User enters email → we check their device fingerprint.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Step 1 – Identify user by email and fingerprint.
     * Decides which auth method to present next.
     */
    public function identify(Request $request)
    {
        $request->validate([
            'email'              => ['required', 'email'],
            'device_fingerprint' => ['required', 'string', 'max:255'],
        ]);

        $this->ensureIsNotRateLimited($request);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey($request));
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Store email + fingerprint in session for next step
        session([
            'auth.email'              => $user->email,
            'auth.device_fingerprint' => $request->device_fingerprint,
        ]);

        $isTrusted = $user->isTrustedDevice($request->device_fingerprint);

        // ── Routing Logic ─────────────────────────────────────────
        // Trusted device + biometric preferred
        if ($isTrusted && $user->preferred_auth === 'biometric') {
            return redirect()->route('auth.biometric');
        }

        // Trusted device + PIN preferred
        if ($isTrusted && $user->preferred_auth === 'pin' && $user->hasPin()) {
            return redirect()->route('auth.pin');
        }

        // Unknown device or password preferred → full password login
        return redirect()->route('auth.password');
    }

    /**
     * Step 2a – Show password login form.
     */
    public function showPasswordForm()
    {
        if (!session('auth.email')) {
            return redirect()->route('login');
        }
        return view('auth.password', ['email' => session('auth.email')]);
    }

    /**
     * Step 2a – Handle full email + password login.
     */
    public function loginWithPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $email       = session('auth.email');
        $fingerprint = session('auth.device_fingerprint');

        if (!$email) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please try again.']);
        }

        $this->ensureIsNotRateLimited($request);

        if (!Auth::attempt(['email' => $email, 'password' => $request->password], $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        // Trust this device for future logins
        if ($fingerprint) {
            Auth::user()->trustDevice($fingerprint);
        }

        Auth::user()->update(['last_seen_at' => now()]);

        $request->session()->regenerate();
        $request->session()->forget(['auth.email', 'auth.device_fingerprint']);

        // Prompt user to set PIN/biometric if not yet configured
        if (Auth::user()->preferred_auth === 'password' && !Auth::user()->hasPin()) {
            return redirect()->route('auth.setup')->with('prompt_setup', true);
        }

        return redirect()->intended('dashboard');
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // ─── Rate Limiting ─────────────────────────────────────────────

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')) . '|' . $request->ip());
    }
}
