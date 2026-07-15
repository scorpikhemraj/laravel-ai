<?php

use App\Http\Livewire\AiPlayground;
use App\Http\Livewire\Post;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('posts', Post::class)
    ->middleware(['auth'])
    ->name('posts');

Route::get('/ai-playground', AiPlayground::class)
    ->middleware(['auth'])
    ->name('ai.playground');

// Alias so /ai-playgroundui also works
Route::get('/ai-playgroundui', AiPlayground::class)
    ->middleware(['auth'])
    ->name('ai.playground.ui');

use App\Http\Controllers\Auth\BiometricController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PinController;

// ─── Guest-only Auth Routes ────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    // Step 1: Email Identification + Device Fingerprint
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login/identify', [LoginController::class, 'identify'])->name('auth.identify');

    // Step 2a: Password Login
    Route::get('/login/password', [LoginController::class, 'showPasswordForm'])->name('auth.password');
    Route::post('/login/password', [LoginController::class, 'loginWithPassword'])->name('auth.password.submit');

    // Step 2b: PIN Login
    Route::get('/login/pin', [PinController::class, 'showPinForm'])->name('auth.pin');
    Route::post('/login/pin', [PinController::class, 'loginWithPin'])->name('auth.pin.submit');

    // Step 2c: Biometric Login (WebAuthn)
    Route::get('/login/biometric', [BiometricController::class, 'showLoginForm'])->name('auth.biometric');
    Route::post('/login/biometric/options', [BiometricController::class, 'loginOptions'])->name('auth.biometric.options');
    Route::post('/login/biometric', [BiometricController::class, 'login'])->name('auth.biometric.submit');
});

// ─── Logout ────────────────────────────────────────────────────────────────────
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── Authenticated Auth Management Routes ─────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    // ── PIN Management ─────────────────────────────────────────────
    Route::get('/auth/setup-pin', [PinController::class, 'showSetupForm'])->name('auth.setup');
    Route::post('/auth/setup-pin', [PinController::class, 'setupPin'])->name('auth.setup.submit');
    Route::post('/auth/change-pin', [PinController::class, 'changePin'])->name('auth.pin.change');
    Route::delete('/auth/pin', [PinController::class, 'removePin'])->name('auth.pin.remove');

    // ── Biometric Registration ─────────────────────────────────────
    Route::get('/auth/biometric/register', [BiometricController::class, 'showRegisterForm'])->name('auth.biometric.register');
    Route::post('/auth/biometric/register/options', [BiometricController::class, 'registerOptions'])->name('auth.biometric.register.options');
    Route::post('/auth/biometric/register', [BiometricController::class, 'register'])->name('auth.biometric.register.submit');

    // ── Credential Management ──────────────────────────────────────
    Route::get('/auth/credentials', [BiometricController::class, 'credentials'])->name('auth.credentials');
    Route::delete('/auth/credentials/{id}', [BiometricController::class, 'deleteCredential'])->name('auth.credentials.delete');
});

require __DIR__.'/auth.php';

// ─── Livewire Chat Routes ──────────────────────────────────────────────────────
use App\Http\Livewire\Chat;

Route::get('/chat', Chat::class)
    ->middleware(['auth'])
    ->name('chat');

// ─── Leads CRUD Routes (Vue + PrimeVue) ─────────────────────────────────────────
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\EventController;

Route::get('/leads', function () {
    return view('leads.index');
})->middleware(['auth'])->name('leads.index');

Route::get('/calendar', function () {
    return view('calendar');
})->middleware(['auth'])->name('calendar');

Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/leads', [LeadController::class, 'index'])->withoutMiddleware('auth'); // Keep lead compatibility if needed
    Route::post('/leads', [LeadController::class, 'store'])->withoutMiddleware('auth');
    Route::get('/leads/bulk-delete', [LeadController::class, 'bulkDestroy'])->withoutMiddleware('auth');
    Route::post('/leads/bulk-delete', [LeadController::class, 'bulkDestroy'])->withoutMiddleware('auth');
    Route::post('/leads/bulk-update', [LeadController::class, 'bulkUpdate'])->withoutMiddleware('auth');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->withoutMiddleware('auth');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->withoutMiddleware('auth');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->withoutMiddleware('auth');

    Route::get('/events', [EventController::class, 'index']);
    Route::post('/events', [EventController::class, 'store']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);
});

Route::get('/run-artisan', function () {
    if (request('token') !== 'vercel-secure-artisan-987') {
        abort(403, 'Unauthorized.');
    }
    
    $command = request('command', 'migrate');
    $parameters = [];
    
    if (request('class')) {
        $parameters['--class'] = request('class');
    }
    if (request('force') || $command === 'migrate') {
        $parameters['--force'] = true;
    }
    
    try {
        $exitCode = \Illuminate\Support\Facades\Artisan::call($command, $parameters);
        return response()->json([
            'status' => 'completed',
            'exit_code' => $exitCode,
            'output' => \Illuminate\Support\Facades\Artisan::output(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});
