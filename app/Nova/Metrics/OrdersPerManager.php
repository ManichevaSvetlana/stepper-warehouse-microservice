<?php

namespace App\Nova\Metrics;

use App\Models\Stepper\Manager;
use App\Models\Stepper\Order;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class OrdersPerManager extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        // Используем join для подсчета заказов, относящихся к каждому менеджеру
        return $this->count($request, Order::query()->join('order_managers', 'orders.id', '=', 'order_managers.order_id'), 'order_managers.manager_id')
            ->label(function ($managerId) {
                // Находим менеджера по его ID и возвращаем его имя
                $manager = Manager::find($managerId);
                return $manager ? $manager->name : 'Неизвестный менеджер';
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
        return 'orders-per-manager';
    }
}
