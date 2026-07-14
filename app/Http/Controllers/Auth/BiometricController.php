<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;

class BiometricController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // REGISTRATION (setting up Face ID / Fingerprint)
    // ─────────────────────────────────────────────────────────────

    /**
     * Show the biometric registration page.
     * Requires the user to be authenticated (post-password login).
     */
    public function showRegisterForm()
    {
        return view('auth.biometric-register');
    }

    /**
     * Step 1: Generate a WebAuthn attestation challenge.
     * Called via AJAX from the registration page.
     */
    public function registerOptions(AttestationRequest $request)
    {
        return $request->userless()->secureRegistration()->toCreate();
    }

    /**
     * Step 2: Verify and store the biometric credential.
     */
    public function register(AttestedRequest $request)
    {
        $credential = $request->save();

        if ($request->has('name')) {
            $credential->update(['alias' => $request->input('name')]);
        }

        // Mark preferred auth method as biometric
        Auth::user()->update(['preferred_auth' => 'biometric']);

        return response()->json([
            'success' => true,
            'message' => 'Biometric authentication registered successfully!',
        ])->cookie('webauthn_current_device', $credential->id, 60 * 24 * 365);
    }

    // ─────────────────────────────────────────────────────────────
    // LOGIN (using Face ID / Fingerprint)
    // ─────────────────────────────────────────────────────────────

    /**
     * Show the biometric login page.
     */
    public function showLoginForm()
    {
        if (!session('auth.email')) {
            return redirect()->route('login');
        }

        $user = User::where('email', session('auth.email'))->first();

        return view('auth.biometric', [
            'email' => session('auth.email'),
            'user'  => $user,
        ]);
    }

    /**
     * Step 1: Generate a WebAuthn assertion challenge.
     * Called via AJAX — user identified from session email.
     */
    public function loginOptions(AssertionRequest $request)
    {
        $email = $request->input('email') ?: session('auth.email');

        if (!$email) {
            return $request->toVerify(null);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        return $request->toVerify($user->webAuthnCredentials());
    }

    /**
     * Step 2: Verify biometric assertion and log the user in.
     */
    public function login(AssertedRequest $request)
    {
        $fingerprint = session('auth.device_fingerprint');

        // Perform the login attempt using the WebAuthn credentials
        // AssertedRequest->login() handles both discoverable (resident keys) 
        // and non-discoverable credentials.
        $user = $request->login();

        if (!$user) {
            return response()->json(['error' => 'Biometric verification failed.'], 422);
        }

        // The user is now logged in via the WebAuthn guard/provider
        $user = Auth::user();

        if ($fingerprint) {
            $user->trustDevice($fingerprint);
        }

        $user->update(['last_seen_at' => now()]);

        $request->session()->regenerate();
        $request->session()->forget(['auth.email', 'auth.device_fingerprint']);

        return response()->json([
            'success'  => true,
            'redirect' => route('dashboard'),
        ])->cookie('webauthn_current_device', $request->input('id'), 60 * 24 * 365);
    }

    // ─────────────────────────────────────────────────────────────
    // MANAGEMENT
    // ─────────────────────────────────────────────────────────────

    /**
     * List all registered biometric credentials for the user.
     */
    public function credentials()
    {
        return response()->json(
            Auth::user()->webAuthnCredentials()->get(['id', 'alias', 'created_at'])
        );
    }

    /**
     * Delete a specific biometric credential.
     */
    public function deleteCredential(Request $request, string $credentialId)
    {
        Auth::user()->webAuthnCredentials()
            ->where('id', $credentialId)
            ->delete();

        // If no credentials left, fall back to PIN or password
        $remaining = Auth::user()->webAuthnCredentials()->count();
        if ($remaining === 0) {
            $fallback = Auth::user()->hasPin() ? 'pin' : 'password';
            Auth::user()->update(['preferred_auth' => $fallback]);
        }

        return response()->json(['success' => true]);
    }
}
