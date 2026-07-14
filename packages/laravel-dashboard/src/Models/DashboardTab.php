<?php

namespace Khemraj\LaravelDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DashboardTab extends Model
{
    protected $table = 'dashboard_tabs';

    protected $fillable = [
        'dashboard_id',
        'title',
        'order',
        'layout_config',
    ];

    protected $casts = [
        'layout_config' => 'array',
        'order' => 'integer',
    ];

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class, 'dashboard_id');
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(DashboardWidget::class, 'dashboard_tab_id')->orderBy('order');
    }
}
