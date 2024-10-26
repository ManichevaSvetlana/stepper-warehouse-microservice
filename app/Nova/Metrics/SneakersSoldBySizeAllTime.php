<?php

namespace App\Nova\Metrics;

use Coroowicaksono\ChartJsIntegration\BarChart;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Stepper\Order;
use Illuminate\Support\Facades\DB;

class SneakersSoldBySizeAllTime
{
    public function calculate()
    {
        // Define size range
        $sizeRange = [];
        for ($i = 35; $i <= 50; $i += 0.5) {
            $sizeRange[] = number_format($i, 1, '.', '');
        }

        // Fetch and normalize sizes
        $sizes = Order::query()
            ->leftJoin('stock_orders', 'orders.stock_order_id', '=', 'stock_orders.id')
            ->select(DB::raw("COALESCE(orders.product_size, stock_orders.size) AS size"))
            ->get()
            ->pluck('size')
            ->map(function ($size) {
                return $this->normalizeSize($size);
            })
            ->filter(function ($size) use ($sizeRange) {
                return in_array($size, $sizeRange);
            })
            ->countBy()
            ->sortKeys(); // Sort by size keys in ascending order

        // Format data as required
        return (new BarChart())->title('Sneakers Sold by Size')
            ->animations([
                'enabled' => true,
                'easing' => 'easeinout',
            ])
            ->series([
                [
                    'barPercentage' => 0.5,
                    'label' => 'Sizes',
                    'backgroundColor' => '#3490dc',
                    'data' => $sizes->values()->toArray(),
                ]
            ])
            ->options([
                'xaxis' => [
                    'categories' => $sizes->keys()->toArray()
                ],
            ])
            ->width('2/3');
    }

    protected function normalizeSize($size)
    {
        $size = str_replace(',', '.', trim($size));
        if (strpos($size, '/') !== false) {
            [$numerator, $denominator] = explode('/', $size);
            $fraction = (float) $numerator / (float) $denominator;
            $size = floor($fraction) + ($fraction - floor($fraction) >= 0.67 ? 0.5 : 0);
        }
        return number_format(round(floatval($size) * 2) / 2, 1, '.', '');
    }
}
