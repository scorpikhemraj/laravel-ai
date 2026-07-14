<?php

namespace Khemraj\LaravelDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dashboard extends Model
{
    use SoftDeletes;

    protected $table = 'dashboards';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'layout_settings',
        'is_active',
        'order',
    ];

    protected $casts = [
        'layout_settings' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function tabs(): HasMany
    {
        return $this->hasMany(DashboardTab::class, 'dashboard_id')->orderBy('order');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(DashboardPermission::class, 'dashboard_id');
    }
}
