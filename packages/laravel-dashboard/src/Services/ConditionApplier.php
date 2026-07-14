<?php

namespace Khemraj\LaravelDashboard\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ConditionApplier
{
    public function apply(Builder $query, array $conditions): Builder
    {
        if (empty($conditions)) {
            return $query;
        }

        // Group conditions by group_id
        $grouped = [];
        foreach ($conditions as $condition) {
            $groupId = $condition['group_id'] ?? 0;
            $grouped[$groupId][] = $condition;
        }

        // Apply groups
        $query->where(function (Builder $q) use ($grouped) {
            foreach ($grouped as $groupId => $groupConditions) {
                $q->where(function (Builder $subQ) use ($groupConditions) {
                    foreach ($groupConditions as $index => $cond) {
                        $this->applySingleCondition($subQ, $cond, $index === 0);
                    }
                });
            }
        });

        return $query;
    }

    protected function applySingleCondition(Builder $query, array $cond, bool $isFirst): void
    {
        $field = $cond['field'];
        $operator = $cond['operator'] ?? 'eq';
        $logical = strtoupper($cond['logical'] ?? 'AND');
        $valueType = $cond['value_type'] ?? 'static';
        $value = $cond['value'] ?? null;

        // Resolve dynamic values
        $value = $this->resolveValue($value, $valueType);

        $method = ($isFirst || $logical === 'AND') ? 'where' : 'orWhere';

        switch ($operator) {
            case 'eq':
            case '=':
                $query->{$method}($field, '=', $value);
                break;
            case 'neq':
            case '!=':
            case '<>':
                $query->{$method}($field, '!=', $value);
                break;
            case 'gt':
            case '>':
                $query->{$method}($field, '>', $value);
                break;
            case 'gte':
            case '>=':
                $query->{$method}($field, '>=', $value);
                break;
            case 'lt':
            case '<':
                $query->{$method}($field, '<', $value);
                break;
            case 'lte':
            case '<=':
                $query->{$method}($field, '<=', $value);
                break;
            case 'in':
                $inMethod = ($isFirst || $logical === 'AND') ? 'whereIn' : 'orWhereIn';
                $query->{$inMethod}($field, (array)$value);
                break;
            case 'not_in':
                $notInMethod = ($isFirst || $logical === 'AND') ? 'whereNotIn' : 'orWhereNotIn';
                $query->{$notInMethod}($field, (array)$value);
                break;
            case 'contains':
                $query->{$method}($field, 'LIKE', '%' . $value . '%');
                break;
            case 'starts_with':
                $query->{$method}($field, 'LIKE', $value . '%');
                break;
            case 'ends_with':
                $query->{$method}($field, 'LIKE', '%' . $value);
                break;
            case 'is_null':
                $nullMethod = ($isFirst || $logical === 'AND') ? 'whereNull' : 'orWhereNull';
                $query->{$nullMethod}($field);
                break;
            case 'is_not_null':
                $notNullMethod = ($isFirst || $logical === 'AND') ? 'whereNotNull' : 'orWhereNotNull';
                $query->{$notNullMethod}($field);
                break;
            case 'between':
                $betweenMethod = ($isFirst || $logical === 'AND') ? 'whereBetween' : 'orWhereBetween';
                $query->{$betweenMethod}($field, (array)$value);
                break;
        }
    }

    protected function resolveValue(mixed $value, string $valueType): mixed
    {
        if ($valueType === 'dynamic') {
            if (is_string($value) && str_starts_with($value, 'current_')) {
                return $this->resolveDynamicValue($value);
            }
        }

        switch ($valueType) {
            case 'current_user':
                return Auth::id();
            case 'current_user_team':
                return Auth::user()?->team_id;
            case 'current_date':
                return Carbon::now()->toDateString();
            case 'current_month_start':
                return Carbon::now()->startOfMonth()->toDateString();
            case 'current_month_end':
                return Carbon::now()->endOfMonth()->toDateString();
            case 'current_year':
                return Carbon::now()->year;
            default:
                return $value;
        }
    }

    protected function resolveDynamicValue(string $key): mixed
    {
        return match ($key) {
            'current_user' => Auth::id(),
            'current_date' => Carbon::now()->toDateString(),
            'current_month_start' => Carbon::now()->startOfMonth()->toDateString(),
            'current_month_end' => Carbon::now()->endOfMonth()->toDateString(),
            'current_year' => Carbon::now()->year,
            default => null,
        };
    }
}
