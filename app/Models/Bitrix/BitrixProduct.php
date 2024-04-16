<?php

namespace App\Models\Bitrix;

use App\Traits\BitrixTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitrixProduct extends Model
{
    use HasFactory, BitrixTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['system_id', 'data', 'sku', 'product_sku'];

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
