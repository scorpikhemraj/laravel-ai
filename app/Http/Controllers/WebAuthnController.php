<?php

namespace App\Http\Controllers;

use App\Skills\WebAuthnSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Exception;

class WebAuthnController extends Controller
{
    protected WebAuthnSkill $webAuthn;

    public function __construct(WebAuthnSkill $webAuthn)
    {
        $this->webAuthn = $webAuthn;
    }

    public function registerOptions(Request $request)
    {
        $user = $request->user();
        if (!$user) {
             // If not logged in, we might want to register during sign up,
             // but for now let's assume we are adding a device to an existing account.
             return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $options = $this->webAuthn->generateRegistrationOptions($user);
            return response()->json($options);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function registerVerify(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $credential = $this->webAuthn->verifyRegistration($user, $request->all(), $request);
            
            if ($request->has('name')) {
                $credential->update(['name' => $request->input('name')]);
            }

            return response()->json(['success' => true])
                ->cookie('webauthn_current_device', $credential->id, 60 * 24 * 365, null, null, false, false);
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('WebAuthn Registration Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function authenticateOptions(Request $request)
    {
        // If we know the user (e.g. they typed their email), we can filter credentials.
        $user = null;
        if ($request->has('email')) {
            $user = User::where('email', $request->input('email'))->first();
        }

        try {
            $options = $this->webAuthn->generateAuthenticationOptions($user);
            return response()->json($options);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function authenticateVerify(Request $request)
    {
        try {
            $credential = $this->webAuthn->verifyAuthentication($request->all(), $request);
            Auth::login($credential->user);
            
            return response()->json(['success' => true, 'redirect' => route('dashboard')])
                ->cookie('webauthn_current_device', $credential->id, 60 * 24 * 365, null, null, false, false);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
