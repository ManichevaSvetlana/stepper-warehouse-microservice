<?php

namespace App\Models;

use App\Traits\BitrixTrait;
use App\Traits\PoizonTrait;
use App\Traits\ShopTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, BitrixTrait, PoizonTrait, ShopTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['data', 'sku', 'type'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->bitrixUrl = env('BITRIX24_WEBHOOK_URL');
        parent::__construct($attributes);
    }
}
