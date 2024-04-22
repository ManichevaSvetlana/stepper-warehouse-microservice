<?php

namespace App\Console\Commands;

use App\Models\Bitrix\BitrixProduct;
use App\Models\Feature;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBitrixData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:update-bitrix-data {--section=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bitrix: update local data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $section = $this->option('section');
        $bitrix = new BitrixProduct();

        $ids = [];
        $bitrixFeatures = $bitrix->listBitrixProperties();
        if ($section === 'all' || $section === 'features') {
            foreach ($bitrixFeatures as $bitrixFeature) {
                $ids[] = $bitrixFeature['id'];
                Feature::updateOrCreate([
                    'system_id' => $bitrixFeature['id'],
                ], [
                    'data' => $bitrixFeature,
                    'type' => 'property',
                    'system' => 'bitrix',
                ]);
            }
            Feature::where('system', 'bitrix')
                ->whereNotIn('system_id', $ids)
                ->delete();
        }


        if ($section === 'all' || $section === 'products') {
            $ids = [];
            $firstElementID = false;
            $page = 1;
            $shopSkuRandom = 'shop-' . rand(0, 100000000);
            $shopProductSkuRandom = 'shop-' . rand(0, 100000000);

            do {
                $bitrixProducts = $bitrix->listBitrixParentProducts($page);
                foreach ($bitrixProducts as $bitrixProduct) {
                    if(!$firstElementID) {
                        $firstElementID = $bitrixProduct['id'];
                    }
                    $ids[] = $bitrixProduct['id'];
                    echo "ID bitrix product - : {$bitrixProduct['id']}\n";
//                    try {
                        BitrixProduct::updateOrCreate(
                            ['system_id' => $bitrixProduct['id']],
                            [
                                'data' => $bitrixProduct,
                                'sku' => $bitrixProduct['type'] ? ($bitrixProduct['property120']['value'] ?? $shopSkuRandom) : $shopSkuRandom,
                                'product_sku' => $bitrixProduct['type'] ? ($bitrixProduct['property128']['value'] ?? $shopProductSkuRandom) : $shopProductSkuRandom,
                            ]
                        );
                    /*} catch (\Exception $exception) {
                        echo $exception->getMessage();
                    }*/
                }

                $page++;
            } while (!empty($bitrixProducts) || $firstElementID !== $bitrixProducts[0]['id'] || count($bitrixProducts) < 50 || $page >= 500);

            BitrixProduct::whereNotIn('system_id', $ids)
                ->delete();
        }
    }
}
