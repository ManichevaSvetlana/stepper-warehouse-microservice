<?php

namespace App\Nova\Metrics;

use App\Models\Stepper\Order;
use Coroowicaksono\ChartJsIntegration\BarChart;
use Laravel\Nova\Http\Requests\NovaRequest;

class SneakersSoldByBrand
{
    public $name = 'Бренды (за все время)';

    public function calculate()
    {
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
            ->selectRaw("
                CASE
                    " . implode(" ", $cases) . "
                    ELSE 'Другие'
                END AS brand_name,
                COUNT(*) AS sold_count
            ")
            ->groupBy('brand_name');

        $results = $query->get()->pluck('sold_count', 'brand_name');

        return (new BarChart())
            ->title('Sneakers Sold by Brand')
            ->series([
                [
                    'label' => 'Brand Sales',
                    'backgroundColor' => '#FF5733', // Custom color for the bars
                    'data' => $results->values()->toArray(),
                ]
            ])
            ->options([
                'xaxis' => [
                    'categories' => $results->keys()->toArray()
                ],
            ]);
    }

    public function uriKey()
    {
        return 'sneakers-sold-by-brand-chart';
    }
}
