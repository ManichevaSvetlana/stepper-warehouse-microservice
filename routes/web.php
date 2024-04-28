<?php

use Illuminate\Support\Facades\Route;

Route::get('/test333', function () {

});

Route::get('/test1', function () {
    $links = [
        5085994,
        8411671,
        5254480,
        1488865,
        48251,
        4553660,
        3017040,
        /*'https://dw4.co/t/A/23rIcvy4',
        'https://dw4.co/t/A/23rIipwC',
        'https://dw4.co/t/A/23rIokvx',
        'https://dw4.co/t/A/23rN7mJU',
        'https://dw4.co/t/A/23rJ4JJH',
        'https://dw4.co/t/A/23rjFmhC',
        'https://dw4.co/t/A/23rjN7vo',
        'https://dw4.co/t/A/23rjSmLU',
        'https://dw4.co/t/A/23rjaJtB',
        'https://dw4.co/t/A/23rO0K7e',
        'https://dw4.co/t/A/23rjtkDW',
        'https://dw4.co/t/A/23rOBfxC',
        'https://dw4.co/t/A/23rOH6YP',
        'https://dw4.co/t/A/23rk9VUA',
        'https://dw4.co/t/A/23rOSdJa',
        'https://dw4.co/t/A/23rkLQEH',
        'https://dw4.co/t/A/23rkX1RG',
        'https://dw4.co/t/A/23rkeaoh',
        'https://dw4.co/t/A/23rkkhAB',
        'https://dw4.co/t/A/23rl1E3u',
        'https://dw4.co/t/A/23rPJELb',
        'https://dw4.co/t/A/23rPPZ2s',
        'https://dw4.co/t/A/23rPV7pj',
        'https://dw4.co/t/A/23rlOlx1',
        'https://dw4.co/t/A/23rlVhET',
        'https://dw4.co/t/A/23rlc6MG',
        'https://dw4.co/t/A/23rPvsTk',
        'https://dw4.co/t/A/23rlq0wS',
        'https://dw4.co/t/A/23rm3atU',
        'https://dw4.co/t/A/23rmJFz2',
        'https://dw4.co/t/A/23rmYBVs',
        'https://dw4.co/t/A/23rQqt5Z',
        'https://dw4.co/t/A/23rQwvO9',
        'https://dw4.co/t/A/23rR1NeE',
        'https://dw4.co/t/A/23rmueul',
        'https://dw4.co/t/A/23rRCPdC',
        'https://dw4.co/t/A/23rn9TYR',
        'https://dw4.co/t/A/23rRweZF',
        'https://dw4.co/t/A/23rS5J2Q',
        'https://dw4.co/t/A/23rSEIVS',
        'https://dw4.co/t/A/23ro8UTc',
        'https://dw4.co/t/A/23rSYnFb',
        'https://dw4.co/t/A/23roScYP',
        'https://dw4.co/t/A/23rocv9J',
        'https://dw4.co/t/A/23rTeyYF',
        'https://dw4.co/t/A/23rqO793',
        'https://dw4.co/t/A/23rqm12J',
        'https://dw4.co/t/A/23rr0ZkR',
        'https://dw4.co/t/A/23rrNR8T',
        'https://dw4.co/t/A/23rW2BiX',
        'https://dw4.co/t/A/23rWBQty',
        'https://dw4.co/t/A/23rsovtQ',
        'https://dw4.co/t/A/23rsvufT',
        'https://dw4.co/t/A/23rXuVt1',
        'https://dw4.co/t/A/23ru3ExZ',
        'https://dw4.co/t/A/23rYULrM'*/];
    $skus = [];
    $poizon = new \App\Models\Poizon\PoizonProduct();

    foreach ($links as $link) {
        $sku = $link;
        echo $sku . '<br>';
        \App\Models\System\TrackProduct::updateOrCreate(['sku' => $sku, 'system' => 'poizon-shop'], ['type' => 'shoes']);
        $skus[] = $sku;
    }

    dd($skus);
});

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__ . '/auth.php';
