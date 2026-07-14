<?php

namespace Khemraj\LaravelDashboard\Services;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DateRangeFilter
{
    public function apply(Builder $query, string $dateColumn, ?string $range, ?string $from = null, ?string $to = null): Builder
    {
        if (empty($range)) {
            return $query;
        }

        $now = Carbon::now();

        switch ($range) {
            case 'today':
                return $query->whereDate($dateColumn, $now->toDateString());

            case 'yesterday':
                return $query->whereDate($dateColumn, $now->subDay()->toDateString());

            case 'this_week':
                return $query->whereBetween($dateColumn, [
                    $now->startOfWeek()->toDateTimeString(),
                    $now->endOfWeek()->toDateTimeString()
                ]);

            case 'last_week':
                $start = Carbon::now()->subWeek()->startOfWeek();
                $end = Carbon::now()->subWeek()->endOfWeek();
                return $query->whereBetween($dateColumn, [$start->toDateTimeString(), $end->toDateTimeString()]);

            case 'this_month':
                return $query->whereBetween($dateColumn, [
                    $now->startOfMonth()->toDateTimeString(),
                    $now->endOfMonth()->toDateTimeString()
                ]);

            case 'last_month':
                $start = Carbon::now()->subMonth()->startOfMonth();
                $end = Carbon::now()->subMonth()->endOfMonth();
                return $query->whereBetween($dateColumn, [$start->toDateTimeString(), $end->toDateTimeString()]);

            case 'last_7_days':
                return $query->where($dateColumn, '>=', Carbon::now()->subDays(7)->toDateTimeString());

            case 'last_30_days':
                return $query->where($dateColumn, '>=', Carbon::now()->subDays(30)->toDateTimeString());

            case 'last_90_days':
                return $query->where($dateColumn, '>=', Carbon::now()->subDays(90)->toDateTimeString());

            case 'this_quarter':
                return $query->whereBetween($dateColumn, [
                    $now->startOfQuarter()->toDateTimeString(),
                    $now->endOfQuarter()->toDateTimeString()
                ]);

            case 'last_quarter':
                $start = Carbon::now()->subMonths(3)->startOfQuarter();
                $end = Carbon::now()->subMonths(3)->endOfQuarter();
                return $query->whereBetween($dateColumn, [$start->toDateTimeString(), $end->toDateTimeString()]);

            case 'this_year':
                return $query->whereYear($dateColumn, $now->year);

            case 'last_year':
                return $query->whereYear($dateColumn, $now->subYear()->year);

            case 'custom':
                if ($from && $to) {
                    return $query->whereBetween($dateColumn, [
                        Carbon::parse($from)->startOfDay()->toDateTimeString(),
                        Carbon::parse($to)->endOfDay()->toDateTimeString()
                    ]);
                }
                if ($from) {
                    return $query->where($dateColumn, '>=', Carbon::parse($from)->startOfDay()->toDateTimeString());
                }
                if ($to) {
                    return $query->where($dateColumn, '<=', Carbon::parse($to)->endOfDay()->toDateTimeString());
                }
                return $query;

            default:
                return $query;
        }
    }
}
