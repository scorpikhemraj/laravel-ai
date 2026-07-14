<?php

namespace Khemraj\LaravelDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardFlowchartEdge extends Model
{
    protected $table = 'dashboard_flowchart_edges';

    protected $fillable = [
        'widget_id',
        'source_node_key',
        'target_node_key',
        'label',
        'edge_type',
        'style',
    ];

    protected $casts = [
        'style' => 'array',
    ];

    public function widget(): BelongsTo
    {
        return $this->belongsTo(DashboardWidget::class, 'widget_id');
    }
}
