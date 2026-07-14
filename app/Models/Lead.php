<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'title',
        'city',
        'country',
        'status',
        'source',
        'value',
        'is_favorite',
        'address',
        'state',
        'postal_code',
        'industry',
        'annual_revenue',
        'number_of_employees',
        'website',
        'linkedin_url',
        'lead_score',
        'notes',
        'converted_at',
        'user_id',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'annual_revenue' => 'decimal:2',
        'number_of_employees' => 'integer',
        'lead_score' => 'integer',
        'converted_at' => 'datetime',
        'is_favorite' => 'boolean',
    ];

    /**
     * Get the user (sales rep) assigned to the lead.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Lead>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the opportunities associated with the lead.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Opportunity>
     */
    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }
}
