<?php

namespace App\Models\Poizon;

use App\Traits\PoizonShopTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoizonShopProduct extends Model
{
    use HasFactory, PoizonShopTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['data', 'sku', 'type', 'updated_at', 'created_at', 'popularity', 'easy_return', 'easy_return_max_cny_price', 'easy_return_sizes', 'has_discount', 'visible_discount', 'real_discount'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];
}
