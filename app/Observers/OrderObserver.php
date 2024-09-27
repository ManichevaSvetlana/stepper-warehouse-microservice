<?php

namespace App\Observers;


use App\Models\Stepper\Order;

class OrderObserver
{
    public function creating(Order $order)
    {
        if($order->is_online_order && $order->price) {
            if(!$order->first_payment) {
                $order->first_payment = $order->price / 2;
            }
            if(!$order->second_payment) {
                $order->second_payment = $order->price / 2;
            }
        }
    }

    public function updating(Order $order)
    {
        if($order->is_online_order && $order->price) {
            if(!$order->first_payment) {
                $order->first_payment = $order->price / 2;
            }
            if(!$order->second_payment) {
                $order->second_payment = $order->price / 2;
            }
        }
    }
}
