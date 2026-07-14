<?php

namespace Khemraj\LaravelDashboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Khemraj\LaravelDashboard\Models\DashboardWidget;
use Khemraj\LaravelDashboard\Models\DashboardTab;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardWidgetController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dashboard_tab_id' => 'required|exists:dashboard_tabs,id',
            'title' => 'required|string|max:255',
            'widget_type' => 'required|string',
            'style_config' => 'nullable|array',
            'grid_position' => 'nullable|array',
            'order' => 'integer',
            'data_sources' => 'nullable|array',
            'data_sources.*.module' => 'required|string',
            'data_sources.*.x_axis_field' => 'nullable|string',
            'data_sources.*.x_axis_type' => 'nullable|string',
            'data_sources.*.y_axis_field' => 'nullable|string',
            'data_sources.*.y_axis_aggregate' => 'nullable|string',
            'data_sources.*.y_axis_group_by' => 'nullable|string',
            'data_sources.*.date_range' => 'nullable|string',
            'data_sources.*.limit' => 'nullable|integer',
            'data_sources.*.conditions' => 'nullable|array',
        ]);

        $widget = DB::transaction(function () use ($validated) {
            $widgetData = collect($validated)->except('data_sources')->toArray();
            $widget = DashboardWidget::create($widgetData);

            if (!empty($validated['data_sources'])) {
                foreach ($validated['data_sources'] as $ds) {
                    $source = $widget->dataSources()->create(collect($ds)->except('conditions')->toArray());

                    if (!empty($ds['conditions'])) {
                        foreach ($ds['conditions'] as $cond) {
                            $cond['widget_id'] = $widget->id;
                            $source->conditions()->create($cond);
                        }
                    }
                }
            }

            return $widget;
        });

        return response()->json([
            'success' => true,
            'message' => 'Widget created successfully.',
            'data' => $widget->load('dataSources.conditions')
        ], 201);
    }

    public function update(Request $request, DashboardWidget $widget): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'widget_type' => 'string',
            'style_config' => 'nullable|array',
            'grid_position' => 'nullable|array',
            'order' => 'integer',
            'data_sources' => 'nullable|array',
            'data_sources.*.id' => 'nullable|integer',
            'data_sources.*.module' => 'required|string',
            'data_sources.*.x_axis_field' => 'nullable|string',
            'data_sources.*.x_axis_type' => 'nullable|string',
            'data_sources.*.y_axis_field' => 'nullable|string',
            'data_sources.*.y_axis_aggregate' => 'nullable|string',
            'data_sources.*.y_axis_group_by' => 'nullable|string',
            'data_sources.*.date_range' => 'nullable|string',
            'data_sources.*.limit' => 'nullable|integer',
            'data_sources.*.conditions' => 'nullable|array',
        ]);

        $widget = DB::transaction(function () use ($widget, $validated) {
            $widgetData = collect($validated)->except('data_sources')->toArray();
            $widget->update($widgetData);

            if (isset($validated['data_sources'])) {
                // Delete removed data sources
                $incomingIds = collect($validated['data_sources'])->pluck('id')->filter()->toArray();
                $widget->dataSources()->whereNotIn('id', $incomingIds)->delete();

                foreach ($validated['data_sources'] as $ds) {
                    if (isset($ds['id'])) {
                        $source = $widget->dataSources()->find($ds['id']);
                        if ($source) {
                            $source->update(collect($ds)->except(['id', 'conditions'])->toArray());
                        }
                    } else {
                        $source = $widget->dataSources()->create(collect($ds)->except('conditions')->toArray());
                    }

                    if ($source) {
                        // Recreate conditions for simplicity or update
                        $source->conditions()->delete();
                        if (!empty($ds['conditions'])) {
                            foreach ($ds['conditions'] as $cond) {
                                $cond['widget_id'] = $widget->id;
                                $source->conditions()->create($cond);
                            }
                        }
                    }
                }
            }

            return $widget;
        });

        return response()->json([
            'success' => true,
            'message' => 'Widget updated successfully.',
            'data' => $widget->load('dataSources.conditions')
        ]);
    }

    public function destroy(DashboardWidget $widget): JsonResponse
    {
        $widget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Widget deleted successfully.'
        ]);
    }

    public function updatePositions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|exists:dashboard_widgets,id',
            'widgets.*.grid_position' => 'required|array',
            'widgets.*.order' => 'required|integer',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['widgets'] as $w) {
                DashboardWidget::where('id', $w['id'])->update([
                    'grid_position' => $w['grid_position'],
                    'order' => $w['order'],
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Positions updated successfully.'
        ]);
    }
}
