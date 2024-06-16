<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class ApplicationController extends Controller
{
    /**
     * Создание товара в магазине
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createProductInShop(Request $request): \Illuminate\Http\JsonResponse
    {
        $sku = $request->input('sku');
        $isStock = $request->input('is_stock') ?? true;
        if($isStock) {
            $sizes = $request->input('sizes');
            $prices = $request->input('prices');
            $presence = $request->input('presence');

            // Формирование аргументов для команды
            $arguments = [
                '--sku' => $sku,
                '--sizes' => $sizes,
                '--prices' => $prices,
                '--presence' => $presence,
            ];
        } else {
            $arguments = [
                '--sku' => $sku,
                '--stock' => 0,
            ];
        }

        // Запуск консольной команды
        $output = new BufferedOutput();
        Artisan::call('poizon:upload-product-to-shop', $arguments, $output);

        // Получение вывода команды
        $commandOutput = $output->fetch();

        // Возврат ответа с результатом выполнения команды
        return response()->json([
            'status' => 'success',
        ]);
    }
}
