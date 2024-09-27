<?php

namespace App\Console\Commands;

use App\Models\Stepper\Order;
use App\Models\Stepper\StockOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportCsv extends Command
{
    protected $signature = 'import:csv {model}';
    protected $description = 'Imports data from a CSV file into the model';

    public array $notificationStatuses = [
        'Ожидание' => 'waiting',
        'Забрали с почты' => 'picked_up',
        'Уведомили о поступлении' => 'notified',
        'Клиент принял заказ' => 'order_accepted',
        'Возврат предоплаты' => 'prepayment_returned',
        'Обмен' => 'exchange',
        'Ожидаем отзыв' => 'review_expected'
    ];

    public array $deliveryStatuses = [
        'Not processed' => 'not_processed',
        'Expected' => 'expected',
        'In sort centre' => 'in_sort_centre',
        'On it\'s way' => 'on_its_way',
        'Customs clearense' => 'customs_clearance',
        'Pick up' => 'pick_up'
    ];

    // php artisan import:csv stock_order

    public function handle(): void
    {
        $model = $this->argument('model');

        if ($model === 'stock_order' || $model === 'all') {
            $file = base_path('import/stock_orders.csv');
            $data = file($file);
            $this->importStockModel($data);
        }

       if ($model === 'order_online' || $model === 'all') {
           $fileOrders = base_path('import/online_sales.csv');
           $fileStockOrders = base_path('import/online_sales_orders.csv');

           $dataOrders = file($fileOrders);
           $dataStockOrders = file($fileStockOrders);
           $this->importOrderModel($dataStockOrders, $dataOrders);
       }

       if ($model === 'order_offline' || $model === 'all') {
           $fileOrders = base_path('import/offline_sales.csv');

           $dataOrders = file($fileOrders);
           $this->importOfflineOrderModel($dataOrders);
       }

        $this->info('Import completed successfully.');
    }

    public function importOfflineOrderModel($dataOrders): void
    {
        foreach ($dataOrders as $index => $line) {
            if ($index == 0) continue; // Пропускаем заголовок

            $csv = str_getcsv($line);
            $dateOfOrder = $csv[2] && strlen($csv[2]) === 10 ? Carbon::createFromFormat('d.m.Y', str_replace(['-', '/', ','], '.', $csv[2]))->format('Y-m-d') : Carbon::today();

            Order::create([
                'is_online_order' => 0,
                'product_name' => $csv[0] ?? '',
                'product_article' => ' ',
                'product_size' => $csv[1] ?? '',
                'date_of_order' => $dateOfOrder,
                'price' => floatval($csv[3]) ?? null,
                'comment' => $csv[4] ?? null,
                'source' => $csv[5] ?? null,
                'sale_value' => $csv[7] ?? null,
                'created_at' => $dateOfOrder,
                'updated_at' => $dateOfOrder,
            ]);
        }
    }

    public function importOrderModel($dataStockOrders, $dataOrders): void
    {
        $stockOrders = [];
        foreach ($dataStockOrders as $index => $line) {
            if ($index == 0) continue; // Пропускаем заголовок

            $csv = str_getcsv($line);
            $orderNumber = $csv[0];
            if(!key_exists(6, $csv)) dd($csv);

            // Сохраняем данные по номеру заказа в массив
            $stockOrders[$orderNumber] = [
                'cny_price' => $csv[2] ?? null,
                'poizon_date' => $csv[4] && strlen($csv[4]) > 6 ? Carbon::createFromFormat('d.m.Y', $csv[4])->format('Y-m-d') : null,
                'track_number' => $csv[5] ?? null,
                'is_on_control' => strtolower($csv[6]) === 'true' ? 1 : 0,
                'onex_status' => $csv[8] ?? null,
                'onex_date' => $csv[9] && strlen($csv[9]) > 6 ? Carbon::createFromFormat('d.m.Y', $csv[9])->format('Y-m-d') : null,
            ];
        }

        foreach ($dataOrders as $index => $line) {
            if ($index == 0) continue; // Пропускаем заголовок

            $csv = str_getcsv($line);
            $orderNumber = $csv[0];

            $stockOrder = $stockOrders[$orderNumber] ?? null;

            $contactValue = $csv[13];
            $contactType = null;
            if($contactValue) {
                // if tg or telegram or t.me in contact value - contact type is telegram
                if (preg_match('/tg|Tg|telegram|телеграм|t.me/i', $contactValue)) {
                    $contactType = 'telegram';
                }
                // if wa or whatsapp or w.me or wts or Whatsapp or вотсап in contact value - contact type is whatsapp
                if (preg_match('/wa|Whatsapp|whatsapp|wts|w.me|вотсап/i', $contactValue)) {
                    $contactType = 'whatsapp';
                }
                // if inst or instagram or insta or in.sta or in.stagram or ins or Inst or Инст in contact value - contact type is instagram
                if (preg_match('/inst|instagram|insta|in.sta|in.stagram|ins|Inst|Инст/i', $contactValue)) {
                    $contactType = 'instagram';
                }
            }

            $dateOfOrder = $csv[6] && strlen($csv[6]) === 10 ? Carbon::createFromFormat('d.m.Y', str_replace(['-', '/', ','], '.', $csv[6]))->format('Y-m-d') : Carbon::today();

            $order = Order::create([
                'is_online_order' => 1,
                'order_site_id' => $orderNumber,
                'product_name' => $csv[1] ?? '',
                'product_article' => ' ',
                'product_size' => $csv[3] ?? ' ',
                'product_link' => $csv[5] ?? '',
                'date_of_order' => $dateOfOrder,
                'price' => floatval($csv[9]) ?? null,
                'first_payment' => floatval($csv[10]) ?? null,
                'second_payment' => floatval($csv[11]) ?? null,
                'is_fully_paid' => strtolower($csv[12]) === 'true' ? 1 : 0,
                'status_notification' => $this->notificationStatuses[trim($csv[14])] ?? 'waiting',
                'status_delivery' => $this->deliveryStatuses[trim($csv[15])] ?? 'not_processed',
                'sale_value' => $csv[16] ?? null,
                'contact_value' => $contactValue ?? null,
                'contact_type' => $contactType ?? 'other',

                // Данные из второго файла (если они есть)
                'cny_price' => floatval($stockOrder['cny_price']) ?? null,
                'is_ordered' => isset($stockOrder) ? 1 : 0,
                'poizon_date' => $stockOrder['poizon_date'] ?? null,
                'track_number' => $stockOrder['track_number'] ?? null,
                'is_on_control' => $stockOrder['is_on_control'] ?? 0,
                'flight_date' => $stockOrder['flight_date'] ?? null,
                'onex_status' => $stockOrder['onex_status'] ?? null,
                'onex_date' => $stockOrder['onex_date'] ?? null,

                'created_at' => $dateOfOrder,
                'updated_at' => $dateOfOrder,
            ]);
        }
    }

    public function importStockModel($data): void
    {
        foreach ($data as $index => $line) {
            if ($index == 0) continue; // Пропускаем заголовок

            $csv = str_getcsv($line);
            StockOrder::create([
                'name' => $csv[1] ?? ' ',
                'article' => $csv[2] ?? ' ',
                'sku' => $csv[3] ?? ' ',
                'size' => $csv[4] ?? '',
                'cny_price' => floatval($csv[5]),
                'poizon_date' => $csv[6] && strlen($csv[6]) > 6 ? Carbon::createFromFormat('d.m.Y', $csv[6])->format('Y-m-d') : null,
                'track_number' => $csv[7],
                'is_on_control' => strtolower($csv[8]) === 'true' ? 1 : 0,
                'onex_status' => $this->deliveryStatuses[$csv[9]] ?? null,
                'onex_date' => $csv[10] && strlen($csv[10]) > 6 ? Carbon::createFromFormat('d.m.Y', $csv[10])->format('Y-m-d') : null,
                'flight_date' => null,
                'comment' => $csv[12] ?? ''
            ]);
        }
    }
}
