<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WebauthnCredential extends Model
{
    /** @use HasFactory<\Database\Factories\WebauthnCredentialFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'type',
        'transports',
        'attestation_type',
        'trust_path',
        'aaguid',
        'credential_id',
        'public_key',
        'user_handle',
        'counter',
        'last_used_at',
    ];

    protected $casts = [
        'transports' => 'array',
        'trust_path' => 'array',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
