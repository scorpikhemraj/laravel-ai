<?php

namespace Khemraj\LaravelDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardFlowchartNode extends Model
{
    protected $table = 'dashboard_flowchart_nodes';

    protected $fillable = [
        'widget_id',
        'node_key',
        'node_type',
        'label',
        'shape',
        'style',
        'position_x',
        'position_y',
        'metadata',
    ];

    protected $casts = [
        'style' => 'array',
        'metadata' => 'array',
        'position_x' => 'float',
        'position_y' => 'float',
    ];

    public function widget(): BelongsTo
    {
        return $this->belongsTo(DashboardWidget::class, 'widget_id');
    }
}
