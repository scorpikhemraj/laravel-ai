<?php

namespace Khemraj\LaravelDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardWidgetCondition extends Model
{
    protected $table = 'dashboard_widget_conditions';

    protected $fillable = [
        'widget_id',
        'data_source_id',
        'field',
        'operator',
        'value',
        'value_type',
        'logical',
        'group_id',
        'order',
    ];

    protected $casts = [
        'value' => 'json',
        'group_id' => 'integer',
        'order' => 'integer',
    ];

    public function widget(): BelongsTo
    {
        return $this->belongsTo(DashboardWidget::class, 'widget_id');
    }

    public function dataSource(): BelongsTo
    {
        return $this->belongsTo(DashboardWidgetDataSource::class, 'data_source_id');
    }
}
