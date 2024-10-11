<?php

namespace App\Nova\Metrics;

use App\Models\Stepper\Order;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class NewOfflineOrders extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        // Логика для диапазонов времени
        switch ($request->range) {
            case 'THIS_MONTH':
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
                break;
            case 'LAST_MONTH':
                $start = now()->subMonth()->startOfMonth();
                $end = now()->subMonth()->endOfMonth();
                break;
            case 'PREVIOUS_MONTH':
                $start = now()->subMonths(2)->startOfMonth();
                $end = now()->subMonths(2)->endOfMonth();
                break;
            case 30:
                $start = now()->subDays(30);
                $end = now();
                break;
            case 'TODAY':
                $start = now()->startOfDay();
                $end = now()->endOfDay();
                break;
            case 365:
                $start = now()->subYear()->startOfDay();
                $end = now()->endOfDay();
                break;
            default:
                // Если диапазон не задан, используем стандартный метод
                return $this->count($request, Order::where('is_online_order', false));
        }

        // Выполнение запроса для указанного диапазона дат
        return $this->result(
            Order::where('is_online_order', false)
                ->whereBetween('created_at', [$start, $end])
                ->count()
        );
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            'THIS_MONTH' => 'Текущий месяц',  // Текущий месяц
            'LAST_MONTH' => 'Прошлый месяц',  // Прошлый месяц
            'PREVIOUS_MONTH' => 'Позапрошлый месяц', // Позапрошлый месяц
            30 => '30 дней',                  // 30 дней
            'TODAY' => 'Сегодня',             // Сегодня
            365 => 'Год',                     // Год
        ];
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int|null
     */
    public function cacheFor()
    {
        return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'new-offline-orders';
    }
}
