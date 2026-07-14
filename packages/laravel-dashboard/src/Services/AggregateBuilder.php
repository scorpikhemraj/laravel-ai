<?php

namespace Khemraj\LaravelDashboard\Services;

use Illuminate\Database\Eloquent\Builder;

class AggregateBuilder
{
    public function apply(Builder $query, ?string $field, ?string $aggregate, string $alias = 'aggregate_value'): Builder
    {
        $aggregate = strtolower($aggregate ?? 'count');
        $field = $field ?: '*';

        if ($field === '*' && $aggregate !== 'count') {
            $field = 'id'; // fallback to ID for non-count aggregates
        }

        switch ($aggregate) {
            case 'sum':
                return $query->selectRaw("SUM({$field}) as {$alias}");
            case 'avg':
                return $query->selectRaw("AVG({$field}) as {$alias}");
            case 'min':
                return $query->selectRaw("MIN({$field}) as {$alias}");
            case 'max':
                return $query->selectRaw("MAX({$field}) as {$alias}");
            case 'count':
            default:
                if ($field === '*') {
                    return $query->selectRaw("COUNT(*) as {$alias}");
                } else {
                    return $query->selectRaw("COUNT({$field}) as {$alias}");
                }
        }
    }
}
