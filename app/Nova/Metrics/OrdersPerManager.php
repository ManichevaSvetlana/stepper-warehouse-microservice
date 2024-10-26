<?php

namespace App\Nova\Metrics;

use App\Models\Stepper\Manager;
use App\Models\Stepper\Order;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class OrdersPerManager extends Partition
{
    public $name = 'Менеджер - заказы (умноженные на 100)';

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Выполняем запрос, подсчитывая долю каждого менеджера в заказах
        $subquery = DB::table('order_managers')
            ->select('order_id', DB::raw('COUNT(manager_id) as manager_count'))
            ->groupBy('order_id');

        $query = Order::query()
            ->join('order_managers', 'orders.id', '=', 'order_managers.order_id')
            ->joinSub($subquery, 'manager_counts', function ($join) {
                $join->on('orders.id', '=', 'manager_counts.order_id');
            })
            ->whereBetween('orders.date_of_order', [$startOfMonth, $endOfMonth])
            ->selectRaw('order_managers.manager_id, SUM(100 * (1.0 / manager_counts.manager_count)) AS manager_share')
            ->groupBy('order_managers.manager_id');

        // Получаем результат в формате, удобном для отображения в метрике Partition
        $results = $query->get()->pluck('manager_share', 'manager_id');

        return $this->result($results->toArray())
            ->label(function ($managerId) {
                $manager = Manager::find($managerId);
                return $manager ? $manager->name : 'Неизвестный менеджер';
            });
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
