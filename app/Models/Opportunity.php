<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'lead_id',
        'user_id',
        'stage',
        'amount',
        'probability',
        'priority',
        'type',
        'lost_reason',
        'expected_close_date',
        'actual_close_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'datetime',
        'actual_close_date' => 'datetime',
    ];

    /**
     * Get the lead that originated this opportunity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Lead, \App\Models\Opportunity>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user (sales rep) assigned to the opportunity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Opportunity>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
