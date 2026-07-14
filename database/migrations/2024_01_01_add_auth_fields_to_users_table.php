<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Device Fingerprint
            $table->string('device_fingerprint')->nullable()->after('remember_token');

            // PIN Login (hashed 4–6 digit PIN)
            $table->string('login_pin')->nullable()->after('device_fingerprint');

            // Trusted devices (JSON array of fingerprints)
            $table->json('trusted_devices')->nullable()->after('login_pin');

            // WebAuthn credentials are stored in a separate table (handled by laragear/webauthn)
            // Track preferred auth method: 'password' | 'pin' | 'biometric'
            $table->enum('preferred_auth', ['password', 'pin', 'biometric'])->default('password')->after('trusted_devices');

            // Track last fingerprint seen
            $table->timestamp('last_seen_at')->nullable()->after('preferred_auth');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'device_fingerprint',
                'login_pin',
                'trusted_devices',
                'preferred_auth',
                'last_seen_at',
            ]);
        });
    }
};
