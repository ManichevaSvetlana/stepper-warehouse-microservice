<?php

namespace App\Models\Stepper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOrder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'article',
        'sku',
        'size',
        'cny_price',
        'price_for_sale',
        'poizon_date',
        'track_number',
        'is_on_control',
        'onex_status',
        'onex_date',
        'flight_date',
        'comment'
    ];
}
