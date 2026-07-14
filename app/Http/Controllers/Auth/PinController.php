<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PinController extends Controller
{
    /**
     * Show the PIN login form.
     */
    public function showPinForm()
    {
        if (!session('auth.email')) {
            return redirect()->route('login');
        }

        return view('auth.pin', [
            'email' => session('auth.email'),
        ]);
    }

    /**
     * Verify PIN and log the user in.
     */
    public function loginWithPin(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'digits_between:4,6'],
        ]);

        $email       = session('auth.email');
        $fingerprint = session('auth.device_fingerprint');

        if (!$email) {
            return redirect()->route('login')->withErrors(['pin' => 'Session expired. Please try again.']);
        }

        $throttleKey = 'pin:' . Str::lower($email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'pin' => "Too many attempts. Try again in {$seconds} seconds.",
            ]);
        }

        $user = User::where('email', $email)->first();

        if (!$user || !$user->verifyPin($request->pin)) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'pin' => 'Invalid PIN. Please try again.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, $request->boolean('remember'));

        // Re-trust device on successful PIN login
        if ($fingerprint) {
            $user->trustDevice($fingerprint);
        }

        $user->update(['last_seen_at' => now()]);

        $request->session()->regenerate();
        $request->session()->forget(['auth.email', 'auth.device_fingerprint']);

        return redirect()->intended('dashboard');
    }

    /**
     * Show PIN setup screen (after first password login).
     */
    public function showSetupForm()
    {
        return view('auth.setup-pin');
    }

    /**
     * Save a new PIN for the authenticated user.
     */
    public function setupPin(Request $request)
    {
        $request->validate([
            'pin'              => ['required', 'digits_between:4,6'],
            'pin_confirmation' => ['required', 'same:pin'],
        ]);

        Auth::user()->setPin($request->pin);
        Auth::user()->update(['preferred_auth' => 'pin']);

        return redirect()->route('dashboard')
            ->with('success', 'PIN set successfully! You can now log in with your PIN on trusted devices.');
    }

    /**
     * Change existing PIN (requires current PIN or password).
     */
    public function changePin(Request $request)
    {
        $request->validate([
            'current_pin' => ['required', 'digits_between:4,6'],
            'new_pin'     => ['required', 'digits_between:4,6', 'different:current_pin'],
            'new_pin_confirmation' => ['required', 'same:new_pin'],
        ]);

        $user = Auth::user();

        if (!$user->verifyPin($request->current_pin)) {
            throw ValidationException::withMessages([
                'current_pin' => 'Current PIN is incorrect.',
            ]);
        }

        $user->setPin($request->new_pin);

        return back()->with('success', 'PIN changed successfully!');
    }

    /**
     * Remove PIN from account.
     */
    public function removePin(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        Auth::user()->update([
            'login_pin'      => null,
            'preferred_auth' => 'password',
        ]);

        return back()->with('success', 'PIN removed from your account.');
    }
}
