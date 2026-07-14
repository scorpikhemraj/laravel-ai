<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyDeviceFingerprint
{
    /**
     * Check if the current device fingerprint matches a trusted device.
     * If not trusted, the user can still access the app but will be prompted
     * to re-verify on sensitive routes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $action = 'warn'): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $fingerprint = $request->header('X-Device-Fingerprint')
            ?? $request->input('device_fingerprint')
            ?? $request->cookie('device_fp');

        $user = Auth::user();

        // No fingerprint at all — skip for now
        if (!$fingerprint) {
            return $next($request);
        }

        $isTrusted = $user->isTrustedDevice($fingerprint);

        // Update last seen
        $user->update(['last_seen_at' => now()]);

        if (!$isTrusted) {
            if ($action === 'block') {
                // Hard block — redirect to re-auth
                return redirect()->route('auth.reauth')
                    ->with('warning', 'Unrecognized device. Please verify your identity.');
            }

            if ($action === 'warn') {
                // Soft warn — allow through but flash a warning
                session()->flash('device_warning', 'This device is not recognized. Consider adding it as a trusted device.');
            }
        }

        return $next($request);
    }
}
