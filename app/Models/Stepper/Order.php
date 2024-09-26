<?php

namespace App\Models\Stepper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_site_id',
        'product_name',
        'product_article',
        'product_link',
        'product_size',
        'date_of_order',
        'onex_date',
        'price',
        'first_payment',
        'second_payment',
        'is_fully_paid',
        'contact_type',
        'contact_value',
        'site_email',
        'site_name',
        'site_phone',
        'status_delivery',
        'status_notification',
        'sale_value',
        'delivery_city',
        'delivery_address',
        'delivery_type',
        'source',
        'comment',
        'cny_price',
        'is_ordered',
        'poizon_date',
        'track_number',
        'is_on_control',
        'flight_date',
        'is_online_order',
        'stock_order_id',
        'sku',
        'price_for_sale',
        'is_transformed_to_stock_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_order' => 'datetime',
        'onex_date' => 'datetime',
        'poizon_date' => 'datetime',
        'flight_date' => 'datetime',
    ];

    /**
     * Get the managers for the order.
     */
    public function managers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Manager::class, 'order_managers');
    }

    /**
     * Get the stock order for the order.
     */
    public function stockOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StockOrder::class);
    }
}
