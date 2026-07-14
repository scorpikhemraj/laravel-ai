<?php

namespace Khemraj\LaravelDashboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Khemraj\LaravelDashboard\Models\DashboardWidget;
use Khemraj\LaravelDashboard\Services\ChartDataBuilder;
use Khemraj\LaravelDashboard\Services\ChartFormatter;
use Illuminate\Http\JsonResponse;

class WidgetDataController extends Controller
{
    protected ChartDataBuilder $dataBuilder;
    protected ChartFormatter $formatter;

    public function __construct(ChartDataBuilder $dataBuilder, ChartFormatter $formatter)
    {
        $this->dataBuilder = $dataBuilder;
        $this->formatter = $formatter;
    }

    public function getData(Request $request, DashboardWidget $widget): JsonResponse
    {
        $widget->load('dataSources.conditions');

        if ($widget->dataSources->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data sources configured for this widget.'
            ], 422);
        }

        $results = [];
        $widgetConditions = $request->input('conditions', []);

        foreach ($widget->dataSources as $source) {
            // Apply request-level date range override if present
            if ($request->filled('date_range')) {
                $source->date_range = $request->input('date_range');
                $source->date_from = $request->filled('date_from') ? now()->parse($request->input('date_from')) : null;
                $source->date_to = $request->filled('date_to') ? now()->parse($request->input('date_to')) : null;
            }

            try {
                $rawResults = $this->dataBuilder->build($source, $widgetConditions);

                // Format results depending on type
                if (in_array(strtolower($widget->widget_type), ['chart', 'bar', 'line', 'pie', 'donut', 'area', 'gauge', 'funnel'])) {
                    $type = in_array(strtolower($widget->widget_type), ['chart'])
                        ? ($source->chart_type ?: 'bar')
                        : $widget->widget_type;

                    $formatted = $this->formatter->format($rawResults, $type, $widget->style_config ?? []);
                    $results[] = [
                        'source_id' => $source->id,
                        'formatted' => $formatted,
                        'raw' => $rawResults
                    ];
                } else {
                    // For number, list, flowchart, return raw structured values
                    $results[] = [
                        'source_id' => $source->id,
                        'raw' => $rawResults
                    ];
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => "Error building data for source #{$source->id}: " . $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'widget_id' => $widget->id,
            'widget_type' => $widget->widget_type,
            'data' => $results
        ]);
    }

    public function previewData(Request $request): JsonResponse
    {
        $request->validate([
            'module' => 'required|string',
            'x_axis_field' => 'nullable|string',
            'y_axis_field' => 'nullable|string',
            'y_axis_aggregate' => 'nullable|string',
            'widget_type' => 'required|string',
        ]);

        $source = new \Khemraj\LaravelDashboard\Models\DashboardWidgetDataSource([
            'module' => $request->input('module'),
            'x_axis_field' => $request->input('x_axis_field'),
            'x_axis_type' => $request->input('x_axis_type', 'field'),
            'y_axis_field' => $request->input('y_axis_field'),
            'y_axis_aggregate' => $request->input('y_axis_aggregate', 'count'),
            'y_axis_group_by' => $request->input('y_axis_group_by'),
            'date_range' => $request->input('date_range', 'last_30_days'),
            'limit' => $request->input('limit', 10),
        ]);

        $conditions = collect($request->input('conditions', []))->map(function ($cond) {
            return new \Khemraj\LaravelDashboard\Models\DashboardWidgetCondition([
                'field' => $cond['field'] ?? '',
                'operator' => $cond['operator'] ?? '=',
                'value' => $cond['value'] ?? '',
            ]);
        });
        $source->setRelation('conditions', $conditions);

        try {
            $rawResults = $this->dataBuilder->build($source);
            
            $widgetType = $request->input('widget_type');
            if (in_array(strtolower($widgetType), ['chart', 'bar', 'line', 'pie', 'donut', 'area', 'gauge', 'funnel'])) {
                $formatted = $this->formatter->format($rawResults, $widgetType, []);
                return response()->json([
                    'success' => true,
                    'formatted' => $formatted,
                    'raw' => $rawResults
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'raw' => $rawResults
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error building data: ' . $e->getMessage()
            ], 500);
        }
    }
}
