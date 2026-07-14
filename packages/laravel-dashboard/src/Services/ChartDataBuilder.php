<?php

namespace Khemraj\LaravelDashboard\Services;

use Khemraj\LaravelDashboard\Models\DashboardWidgetDataSource;
use Khemraj\LaravelDashboard\Models\DashboardWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ChartDataBuilder
{
    protected ModuleRegistry $moduleRegistry;
    protected ConditionApplier $conditionApplier;
    protected AggregateBuilder $aggregateBuilder;
    protected DateRangeFilter $dateRangeFilter;

    public function __construct(
        ModuleRegistry $moduleRegistry,
        ConditionApplier $conditionApplier,
        AggregateBuilder $aggregateBuilder,
        DateRangeFilter $dateRangeFilter
    ) {
        $this->moduleRegistry = $moduleRegistry;
        $this->conditionApplier = $conditionApplier;
        $this->aggregateBuilder = $aggregateBuilder;
        $this->dateRangeFilter = $dateRangeFilter;
    }

    public function build(DashboardWidgetDataSource $dataSource, array $widgetConditions = []): array
    {
        $modelClass = $this->moduleRegistry->resolve($dataSource->module);
        if (!$modelClass) {
            throw new \InvalidArgumentException("Module class not found for slug: {$dataSource->module}");
        }

        /** @var Builder $query */
        $query = $modelClass::query();

        // 1. Apply date range if applicable
        if ($dataSource->date_range && $dataSource->x_axis_field) {
            // Usually filter by created_at or the x_axis_field if it is a date column
            $dateCol = 'created_at';
            // Simple check: if x_axis_field ends with _at or _date or is cast as date, filter by it
            $modelInstance = new $modelClass();
            $casts = $modelInstance->getCasts();
            if (isset($casts[$dataSource->x_axis_field]) && str_contains($casts[$dataSource->x_axis_field], 'date')) {
                $dateCol = $dataSource->x_axis_field;
            }
            $this->dateRangeFilter->apply(
                $query,
                $dateCol,
                $dataSource->date_range,
                $dataSource->date_from?->toDateString(),
                $dataSource->date_to?->toDateString()
            );
        }

        // 2. Apply dynamic conditions
        // Merge data-source conditions with widget-level conditions
        $dsConditions = $dataSource->conditions->toArray();
        $allConditions = array_merge($dsConditions, $widgetConditions);
        $this->conditionApplier->apply($query, $allConditions);

        // 3. Apply Group By & Aggregation
        $xField = $dataSource->x_axis_field;
        $yField = $dataSource->y_axis_field;
        $yAggregate = $dataSource->y_axis_aggregate;
        $groupBy = $dataSource->y_axis_group_by;

        $selects = [];

        if ($xField) {
            // Check x_axis_type (field, date_group, relation)
            if ($dataSource->x_axis_type === 'date_group') {
                // MySQL specific date grouping for aggregation, with SQLite fallback
                $driverName = DB::connection()->getDriverName();
                if ($driverName === 'sqlite') {
                    $selects[] = "strftime('%Y-%m', {$xField}) as x_axis";
                } else {
                    $selects[] = "DATE_FORMAT({$xField}, '%Y-%m') as x_axis";
                }
                $query->groupByRaw('x_axis');
            } else {
                $selects[] = "{$xField} as x_axis";
                $query->groupBy($xField);
            }
        }

        if ($groupBy) {
            $selects[] = "{$groupBy} as group_by";
            $query->groupBy($groupBy);
        }

        // Apply selects to query
        if (!empty($selects)) {
            $query->selectRaw(implode(', ', $selects));
        }

        // Apply aggregate function
        $this->aggregateBuilder->apply($query, $yField, $yAggregate, 'aggregate_value');

        // 4. Apply Sort
        if ($dataSource->sort_field) {
            $query->orderBy($dataSource->sort_field, $dataSource->sort_direction ?: 'asc');
        } elseif ($xField) {
            // default sorting by X-axis alias if grouped by raw
            if ($dataSource->x_axis_type === 'date_group') {
                $query->orderByRaw('x_axis asc');
            } else {
                $query->orderBy($xField, 'asc');
            }
        }

        // 5. Apply Limit
        if ($dataSource->limit) {
            $query->limit($dataSource->limit);
        } else {
            $query->limit(500); // safety cap
        }

        return $query->get()->toArray();
    }
}
