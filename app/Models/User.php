<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Laragear\WebAuthn\WebAuthnAuthentication;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements WebAuthnAuthenticatable
{
    use HasFactory, Notifiable, WebAuthnAuthentication;

    protected $fillable = [
        'name',
        'email',
        'password',
        'device_fingerprint',
        'login_pin',
        'trusted_devices',
        'preferred_auth',
        'last_seen_at',
        'role',
        'target_revenue',
        'department',
        'commission_rate',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'login_pin',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at'      => 'datetime',
        'trusted_devices'   => 'array',
        'password'          => 'hashed',
        'target_revenue'    => 'decimal:2',
        'commission_rate'   => 'decimal:2',
    ];

    // ─── PIN Methods ───────────────────────────────────────────────

    /**
     * Set a hashed PIN for the user.
     */
    public function setPin(string $pin): void
    {
        $this->update(['login_pin' => Hash::make($pin)]);
    }

    /**
     * Verify a given PIN against the stored hash.
     */
    public function verifyPin(string $pin): bool
    {
        return $this->login_pin && Hash::check($pin, $this->login_pin);
    }

    /**
     * Check whether the user has set a PIN.
     */
    public function hasPin(): bool
    {
        return !is_null($this->login_pin);
    }

    // ─── Device Fingerprint Methods ────────────────────────────────

    /**
     * Trust the current device fingerprint.
     */
    public function trustDevice(string $fingerprint): void
    {
        $trusted = $this->trusted_devices ?? [];

        if (!in_array($fingerprint, $trusted)) {
            $trusted[] = $fingerprint;
            // Keep only last 5 trusted devices
            $trusted = array_slice($trusted, -5);
            $this->update(['trusted_devices' => $trusted]);
        }
    }

    /**
     * Check if a fingerprint belongs to a trusted device.
     */
    public function isTrustedDevice(string $fingerprint): bool
    {
        return in_array($fingerprint, $this->trusted_devices ?? []);
    }

    /**
     * Remove a specific trusted device.
     */
    public function removeTrustedDevice(string $fingerprint): void
    {
        $trusted = array_filter(
            $this->trusted_devices ?? [],
            fn($f) => $f !== $fingerprint
        );
        $this->update(['trusted_devices' => array_values($trusted)]);
    }

    /**
     * Remove all trusted devices (force re-auth on all devices).
     */
    public function clearTrustedDevices(): void
    {
        $this->update(['trusted_devices' => []]);
    }

    /**
     * Get the leads assigned to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Lead>
     */
    public function leads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Get the opportunities assigned to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Opportunity>
     */
    public function opportunities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    /**
     * Get the chats the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Chat>
     */
    public function chats(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_user')
            ->withPivot('joined_at')
            ->withTimestamps();
    }
}
