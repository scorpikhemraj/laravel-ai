<?php

namespace Khemraj\LaravelDashboard\Services;

class ChartFormatter
{
    public function format(array $results, string $chartType, array $styleConfig = []): array
    {
        $chartType = strtolower($chartType);

        // Extract styling configurations
        $colors = $styleConfig['color_palette'] ?? ['#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de', '#3ba272', '#fc8452', '#9a60b4', '#ea7ccc'];
        $showLegend = $styleConfig['legend'] ?? true;
        $legendPosition = $styleConfig['legend_position'] ?? 'bottom';
        $showTooltip = $styleConfig['tooltip'] ?? true;
        $showGrid = $styleConfig['grid_lines'] ?? true;
        $animate = $styleConfig['animation'] ?? true;

        // Base ECharts Option structure
        $option = [
            'color' => $colors,
            'animation' => $animate,
            'tooltip' => $showTooltip ? ['trigger' => in_array($chartType, ['pie', 'donut']) ? 'item' : 'axis'] : null,
            'legend' => $showLegend ? [
                'show' => true,
                'bottom' => $legendPosition === 'bottom' ? 0 : null,
                'top' => $legendPosition === 'top' ? 0 : null,
                'left' => $legendPosition === 'left' ? 0 : null,
                'right' => $legendPosition === 'right' ? 0 : null,
            ] : ['show' => false],
            'grid' => [
                'left' => '3%',
                'right' => '4%',
                'bottom' => $showLegend && $legendPosition === 'bottom' ? '10%' : '3%',
                'containLabel' => true,
            ],
        ];

        // Format depending on chart type
        if (in_array($chartType, ['pie', 'donut'])) {
            $formattedData = [];
            foreach ($results as $row) {
                $name = $row['x_axis'] ?? 'Unknown';
                $value = $row['aggregate_value'] ?? 0;
                $formattedData[] = ['name' => $name, 'value' => $value];
            }

            $option['series'] = [[
                'type' => 'pie',
                'radius' => $chartType === 'donut' ? ['40%', '70%'] : '70%',
                'avoidLabelOverlap' => true,
                'itemStyle' => [
                    'borderRadius' => 8,
                    'borderColor' => '#fff',
                    'borderWidth' => 2,
                ],
                'label' => [
                    'show' => $styleConfig['data_labels'] ?? true,
                    'position' => 'outside',
                ],
                'data' => $formattedData,
            ]];
        } elseif ($chartType === 'funnel') {
            $formattedData = [];
            foreach ($results as $row) {
                $name = $row['x_axis'] ?? 'Unknown';
                $value = $row['aggregate_value'] ?? 0;
                $formattedData[] = ['name' => $name, 'value' => $value];
            }

            $option['series'] = [[
                'name' => 'Funnel',
                'type' => 'funnel',
                'left' => '10%',
                'top' => 60,
                'bottom' => 60,
                'width' => '80%',
                'sort' => 'descending',
                'gap' => 2,
                'label' => [
                    'show' => true,
                    'position' => 'inside'
                ],
                'data' => $formattedData,
            ]];
        } elseif ($chartType === 'gauge') {
            $row = reset($results);
            $value = $row ? ($row['aggregate_value'] ?? 0) : 0;
            $name = $row ? ($row['x_axis'] ?? 'Value') : 'Value';

            $option['series'] = [[
                'type' => 'gauge',
                'progress' => [
                    'show' => true,
                    'width' => 18
                ],
                'axisLine' => [
                    'lineStyle' => [
                        'width' => 18
                    ]
                ],
                'detail' => [
                    'valueAnimation' => true,
                    'formatter' => '{value}'
                ],
                'data' => [['value' => $value, 'name' => $name]]
            ]];
        } else {
            // Line, Area, Bar, etc. (standard Cartesian grid)
            $xAxisData = [];
            $seriesData = [];

            // Detect groups if present
            $hasGroups = isset($results[0]['group_by']);

            if ($hasGroups) {
                $groups = [];
                $xValues = [];
                $matrix = [];

                foreach ($results as $row) {
                    $x = $row['x_axis'] ?? 'Unknown';
                    $g = $row['group_by'] ?? 'Default';
                    $val = $row['aggregate_value'] ?? 0;

                    $xValues[$x] = true;
                    $groups[$g] = true;
                    $matrix[$x][$g] = $val;
                }

                $xAxisData = array_keys($xValues);
                $groupNames = array_keys($groups);

                foreach ($groupNames as $g) {
                    $data = [];
                    foreach ($xAxisData as $x) {
                        $data[] = $matrix[$x][$g] ?? 0;
                    }

                    $seriesItem = [
                        'name' => $g,
                        'type' => $chartType === 'area' ? 'line' : $chartType,
                        'data' => $data,
                    ];

                    if ($chartType === 'area') {
                        $seriesItem['areaStyle'] = [];
                    }

                    if ($styleConfig['stacked'] ?? false) {
                        $seriesItem['stack'] = 'total';
                    }

                    $seriesData[] = $seriesItem;
                }
            } else {
                // Single series
                $data = [];
                foreach ($results as $row) {
                    $xAxisData[] = $row['x_axis'] ?? 'Unknown';
                    $data[] = $row['aggregate_value'] ?? 0;
                }

                $seriesItem = [
                    'type' => $chartType === 'area' ? 'line' : $chartType,
                    'data' => $data,
                ];

                if ($chartType === 'area') {
                    $seriesItem['areaStyle'] = [];
                }

                $seriesData[] = $seriesItem;
            }

            $option['xAxis'] = [
                'type' => 'category',
                'data' => $xAxisData,
                'splitLine' => ['show' => $showGrid],
            ];

            $option['yAxis'] = [
                'type' => 'value',
                'splitLine' => ['show' => $showGrid],
            ];

            $option['series'] = $seriesData;
        }

        return $option;
    }
}
