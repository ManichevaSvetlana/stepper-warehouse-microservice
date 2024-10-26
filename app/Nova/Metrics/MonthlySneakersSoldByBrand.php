<?php

namespace App\Nova\Metrics;

use App\Models\Stepper\Order;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class MonthlySneakersSoldByBrand extends Partition
{
    public $name = 'Бренды (за текущий месяц)';

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

        // Бренды и возможные ошибки в их написании
        $brands = [
            'Nike' => ['Nike', 'Nikke', 'Nkie'],
            'Adidas' => ['Adidas', 'Addidas', 'Adiddas'],
            'Puma' => ['Puma', 'Pumma'],
            'Reebok' => ['Reebok', 'Rebok', 'Reboc'],
            'New Balance' => ['New Balance', 'NB', 'NewBal'],
            'Asics' => ['Asics', 'Ascs', 'Aiscs'],
            'Converse' => ['Converse', 'Convers', 'Converce'],
            'Vans' => ['Vans', 'Vanss', 'Vansss'],
            'Jordan' => ['Jordan', 'Jordans', 'Jordn']
        ];

        $cases = [];

        foreach ($brands as $brand => $aliases) {
            $conditions = [];
            foreach ($aliases as $alias) {
                $conditions[] = "LOWER(orders.product_name) LIKE '%$alias%' OR LOWER(stock_orders.name) LIKE '%$alias%'";
            }
            $cases[] = "WHEN " . implode(" OR ", $conditions) . " THEN '$brand'";
        }

        $query = Order::query()
            ->leftJoin('stock_orders', 'orders.stock_order_id', '=', 'stock_orders.id')
            ->whereBetween('orders.date_of_order', [$startOfMonth, $endOfMonth]) // Фильтр на текущий месяц
            ->selectRaw("
                CASE
                    " . implode(" ", $cases) . "
                    ELSE 'Другие'
                END AS brand_name,
                COUNT(*) AS sold_count
            ")
            ->groupBy('brand_name');

        // Получаем результат и отфильтровываем значения ниже определенного порога
        $results = $query->get()->pluck('sold_count', 'brand_name')->toArray();

        return $this->result($results);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'monthly-sneakers-sold-by-brand';
    }
}
