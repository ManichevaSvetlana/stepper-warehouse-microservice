<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    $product = new \App\Models\Shop\ShopProduct();
    $product->setShopAuth();
    dd($product->createShopProducts([
        [
            "title" => [
                "ru" => "iPhone 13 белый",
                "uk" => "iPhone 13 білий",
                "en" => "iPhone 13 white"
            ],
            "parent_title" => [
                "ru" => "iPhone 13",
                "uk" => "iPhone 13",
                "en" => "iPhone 13"
            ],
            "description" => [
                "ru" => "Смартфон iPhone 13, современный, стильный, мощный",
                "uk" => "Смартфон iPhone 13, сучасний, стильний, потужний",
                "en" => "Smartphone iPhone 13, modern, stylish, powerful"
            ],
            "unit" => [
                "ru" => "за 1 товар",
                "uk" => "за 1 товар",
                "en" => "for 1 product"
            ],
            "seo_title" => [
                "ru" => "Купить iPhone 13 в интернет-магазине",
                "uk" => "Придбати iPhone 13 в інтернет-магазині",
                "en" => "Buy iPhone 13 at the internet-shop"
            ],
            "seo_keywords" => [
                "ru" => "iPhone 13, современный, стильный, мощный",
                "uk" => "iPhone 13, сучасний, стильний, потужний",
                "en" => "iPhone 13 at the internet-shop"
            ],
            "seo_description" => [
                "ru" => "Купите смартфон iPhone 13 дешево в нашем интернет-магазине",
                "uk" => "Придбайте смартфон iPhone 13 дешево в нашому інтернет-магазині",
                "en" => "Buy smartphone iPhone 13 for less price at out internet-shop"
            ],
            "brand" => [
                "id" => 254714
            ],
            "parent" => [
                [
                    "id" => 487155
                ]
            ],
            "sku" => "iph-13-20-23",
            "parent_sku" => "iph---",
            "currency" => "UAH",
            "price" => 210,
            "barcode" => 4820002120652,
            "presence" => false,
            "availability" => "Unpublish",
            "residues" => 125,
            "condition" => "New",
            "characteristics" => [
                "ID_304770" => [
                    [
                        "id" => 3030529,
                        "value" => [
                            "ru" => "Белый",
                            "uk" => "Білий",
                            "en" => "White"
                        ]
                    ]
                ],
            ],
            "modification" => [
                "id" => 58481
            ],
            "force_alias_update" => true
        ],
        [
            "title" => [
                "ru" => "iPhone 13 зеленый",
                "uk" => "iPhone 13 зелений",
                "en" => "iPhone 13 green"
            ],
            "description" => [
                "ru" => "Смартфон iPhone 13, современный, стильный, мощный",
                "uk" => "Смартфон iPhone 13, сучасний, стильний, потужний",
                "en" => "Smartphone iPhone 13, modern, stylish, powerful"
            ],
            "seo_title" => [
                "ru" => "Купить iPhone 13 в интернет-магазине",
                "uk" => "Придбати iPhone 13 в інтернет-магазині",
                "en" => "Buy iPhone 13 at the internet-shop"
            ],
            "seo_keywords" => [
                "ru" => "iPhone 13, современный, стильный, мощный",
                "uk" => "iPhone 13, сучасний, стильний, потужний",
                "en" => "iPhone 13 at the internet-shop"
            ],
            "seo_description" => [
                "ru" => "Купите смартфон iPhone 13 дешево в нашем интернет-магазине",
                "uk" => "Придбайте смартфон iPhone 13 дешево в нашому інтернет-магазині",
                "en" => "Buy smartphone iPhone 13 for less price at out internet-shop"
            ],
            "sku" => "iph-13-20-24",
            "parent_sku" => "iph---",
            "currency" => "UAH",
            "price" => 230,
            "price_old" => 400,
            "barcode" => 4820002120653,
            "presence" => false,
            "availability" => "Unpublish",
            "residues" => 125,
            "condition" => "New",
            "characteristics" => [
                "ID_304770" => [
                    [
                        "id" => 314200,
                        "value" => [
                            "ru" => "Зеленый",
                            "uk" => "Зелений",
                            "en" => "Green"
                        ]
                    ]
                ],
            ],
            "force_alias_update" => true
        ]
    ]));
});

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__ . '/auth.php';
