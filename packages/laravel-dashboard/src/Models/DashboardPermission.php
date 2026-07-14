<?php

namespace Khemraj\LaravelDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DashboardPermission extends Model
{
    protected $table = 'dashboard_permissions';

    protected $fillable = [
        'dashboard_id',
        'permissionable_type',
        'permissionable_id',
        'access_level',
    ];

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class, 'dashboard_id');
    }

    public function permissionable(): MorphTo
    {
        return $this->morphTo();
    }
}
