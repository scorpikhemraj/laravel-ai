<?php

namespace Khemraj\LaravelDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DashboardWidgetDataSource extends Model
{
    protected $table = 'dashboard_widget_data_sources';

    protected $fillable = [
        'widget_id',
        'module',
        'label',
        'x_axis_field',
        'x_axis_type',
        'y_axis_field',
        'y_axis_aggregate',
        'y_axis_group_by',
        'sort_field',
        'sort_direction',
        'limit',
        'date_range',
        'date_from',
        'date_to',
    ];

    protected $casts = [
        'limit' => 'integer',
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function widget(): BelongsTo
    {
        return $this->belongsTo(DashboardWidget::class, 'widget_id');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(DashboardWidgetCondition::class, 'data_source_id');
    }
}
