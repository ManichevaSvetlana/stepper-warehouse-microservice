<?php

namespace App\Console\Commands;

use App\Models\Bitrix\BitrixProduct;
use App\Models\Feature;
use Illuminate\Console\Command;

class UpdateBitrixData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:update-bitrix-data';

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
        $bitrix = new BitrixProduct();

        $bitrixFeatures = $bitrix->listBitrixProperties();
        foreach ($bitrixFeatures as $bitrixFeature) {
            Feature::updateOrCreate([
                'system_id' => $bitrixFeature['id'],
            ], [
                'data' => $bitrixFeature,
                'type' => 'property',
                'system' => 'bitrix',
            ]);
        }

        $bitrixProducts = $bitrix->listBitrixParentProducts();
        foreach ($bitrixProducts as $bitrixProduct) {
            BitrixProduct::updateOrCreate(
                ['system_id' => $bitrixProduct['id']],
                [
                    'data' => $bitrixProduct,
                    'sku' => $bitrixProduct['property120']['value'] ?? null,
                ]
            );
        }
    }
}
