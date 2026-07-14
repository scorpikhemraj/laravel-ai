<?php

namespace Khemraj\LaravelDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DashboardWidget extends Model
{
    protected $table = 'dashboard_widgets';

    protected $fillable = [
        'dashboard_tab_id',
        'widget_type',
        'title',
        'style_config',
        'grid_position',
        'refresh_interval',
        'cache_ttl',
        'is_visible',
        'order',
    ];

    protected $casts = [
        'style_config' => 'array',
        'grid_position' => 'array',
        'is_visible' => 'boolean',
        'refresh_interval' => 'integer',
        'cache_ttl' => 'integer',
        'order' => 'integer',
    ];

    public function tab(): BelongsTo
    {
        return $this->belongsTo(DashboardTab::class, 'dashboard_tab_id');
    }

    public function dataSources(): HasMany
    {
        return $this->hasMany(DashboardWidgetDataSource::class, 'widget_id');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(DashboardWidgetCondition::class, 'widget_id');
    }

    public function flowchartNodes(): HasMany
    {
        return $this->hasMany(DashboardFlowchartNode::class, 'widget_id');
    }

    public function flowchartEdges(): HasMany
    {
        return $this->hasMany(DashboardFlowchartEdge::class, 'widget_id');
    }
}
