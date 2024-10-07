<?php

namespace App\Nova\Metrics;

use App\Models\Stepper\Order;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class OrdersPerType extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->count($request, Order::class, 'is_online_order')
            ->label(function ($value) {
                // Преобразуем значения 0 и 1 в текстовые метки
                return $value ? 'Онлайн-заказ' : 'Оффлайн-заказ';
            });
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int|null
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'orders-per-type';
    }
}
