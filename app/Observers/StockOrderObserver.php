<?php

namespace App\Observers;


use App\Models\Stepper\StockOrder;

class StockOrderObserver
{
    public function creating(StockOrder $stockOrder)
    {
        $stockOrder->price_for_sale = $stockOrder->countPriceForSale();
    }

    public function updating(StockOrder $stockOrder)
    {
        $stockOrder->price_for_sale = $stockOrder->countPriceForSale();
    }
}
