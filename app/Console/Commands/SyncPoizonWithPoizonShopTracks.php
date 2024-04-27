<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncPoizonWithPoizonShopTracks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poizon:sync-poizon-with-poizon-shop-tracks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (\App\Models\Poizon\PoizonProduct::all() as $product)
        {
            $sku = $product->data['detail']['spuId'];
            if(\App\Models\System\TrackProduct::where('sku', $sku)->where('system', 'poizon-shop')->exists())
            {
                continue;
            }
            \App\Models\System\TrackProduct::create([
                'sku' => $sku,
                'system' => 'poizon-shop',
                'type' => 'shoes'
            ]);
        }
    }
}
