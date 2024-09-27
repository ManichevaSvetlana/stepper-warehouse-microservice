<?php

namespace App\Models\Stepper;

use App\Console\Commands\SyncSystemsProducts;
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
        'comment',
        'is_on_website'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'poizon_date' => 'date',
        'onex_date' => 'date',
        'flight_date' => 'date',
        'is_on_control' => 'boolean'
    ];

    public function countPriceForSale()
    {
        $mainJob = new SyncSystemsProducts();
        $cnyPrice = $this->cny_price;
        $pricesData = $mainJob->calculatePrice(floatval($cnyPrice), false, true);

        return $pricesData['price'];
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }
}
