<?php

namespace App\Models\Poizon;

use App\Traits\PoizonTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoizonProduct extends Model
{
    use HasFactory, PoizonTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['data', 'sku', 'type', 'prices'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'prices' => 'array',
    ];
}
