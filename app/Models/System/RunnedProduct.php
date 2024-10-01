<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RunnedProduct extends Model
{
    use HasFactory;

    protected $fillable = ['data', 'sku'];

    protected $casts = [
        'data' => 'array',
    ];
}
