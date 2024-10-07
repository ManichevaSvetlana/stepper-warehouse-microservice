<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\NewOfflineOrders;
use App\Nova\Metrics\NewOnlineOrders;
use App\Nova\Metrics\NewOrders;
use App\Nova\Metrics\NewOrdersInMonth;
use App\Nova\Metrics\OrdersPerDay;
use App\Nova\Metrics\OrdersPerManager;
use App\Nova\Metrics\OrdersPerMonths;
use App\Nova\Metrics\OrdersPerType;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            new NewOrders,
            new OrdersPerDay,
            new OrdersPerMonths,
            new OrdersPerType,
            new NewOnlineOrders,
            new NewOfflineOrders,
            new NewOrdersInMonth,
            new OrdersPerManager()
        ];
    }
}
