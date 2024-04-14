<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $product = new App\Models\Product();
    $product->setShopAuth();
    /*$feauture = new App\Models\Feature();
    $feauture->setShopAuth();
    $feauture->updateListOfLocalShopCategories();
    dd($feauture->first()->data);*/


    //dd($product->listPricesBitrix());
    //dd($product->updatePriceToBitrix(73, 1000));
    //dd($product->updateOrCreateProductPriceBitrix(107, 288));
    /*$quantity1 = rand(1, 10);
    $quantity2 = rand(1, 10);
    dd($product->addProductToBitrix([
        'id' => 'ref434',
        'name' => 'Parent Product Test ' . rand(3, 100),
        'quantity' => $quantity1 + $quantity2,
        'price' => rand(100, 2000),
        'size' => rand(35, 45),
        'images' => ['https://cdn.poizon.com/pro-img/origin-img/20240104/fa123948ad144465b461829a673fa8ed.jpg']
    ]));*/

    //dd($product->getPoizonProductData('8089481'));

    /*$products = collect($product->listShopProducts(25, 64));
    dd($products);*/


    /*$parent = $product->createShopProducts([
        [
            "title" => "Test Product from API - 33",
            "seo_title" => "test seo",
            "seo_keywords" => "test seo k",
            "seo_description" => "test seo d",
            "sku" => "TEST-API-33",
            "availability" => "Unpublish",
            "display_in_showcase" => 0,
            "parent" => [
                "value" => "Catalog",
                "id" => 487155
            ],
            "brand" => [
                "value" => "New Balance",
                "id" => 301365
            ]
        ]]);

    $child = $product->createShopProducts([
        [
            "title" => "33 - variation 1",
            "parent_sku" => "TEST-API-33",
            "parent_title" => [
                "en" => "Test Product from API - 33"
            ],
            "sku" => "TEST-API-33-variation-1",
            "price" => 100,
            "currency" => "GEL",
            "availability" => "Unpublish",
            "display_in_showcase" => 0,
            "presence" => 0,
            "images" => [
                "links" => [
                    "https://steppershop.ge/Media/shop-17094/Снимок экрана 2024-04-06 в 16.38.16.png",
                    "https://steppershop.ge/Media/shop-17094/Снимок экрана 2024-04-06 в 16.37.53.png"
                ]
            ],
            "characteristics" => [
                "ID_629285" => [
                    "value" => "39",
                    "id" => 4224048
                ],
                "ID_563598" => [
                    "value" => "Brown",
                    "id" => 4419749
                ],
            ],
            "parent" => [
                "ID_487155" => [
                    "value" => "Catalog",
                    "id" => 487155
                ],
            ],
            "brand" => [
                "value" => "New Balance",
                "id" => 301365
            ],
            "modification" => [
                "value" => "Catalog",
                "id" => 487155
            ],
        ],
        [
            "title" => "33 - variation 2",
            "parent_sku" => "TEST-API-33",
            "parent_title" => [
                "en" => "Test Product from API - 33"
            ],
            "sku" => "TEST-API-33-variation-2",
            "price" => 300,
            "currency" => "GEL",
            "availability" => "Unpublish",
            "display_in_showcase" => 0,
            "presence" => 0,
            "images" => [
                "links" => [
                    "https://steppershop.ge/Media/shop-17094/Снимок экрана 2024-04-06 в 16.37.53.png"
                ]
            ],
            "characteristics" => [
                "ID_629285" => [
                    "value" => "39.5",
                    "id" => 4224060
                ],
                "ID_563598" => [
                    "value" => "Brown",
                    "id" => 4419749
                ],
            ],
            "parent" => [
                "ID_487155" => [
                    "value" => "Catalog",
                    "id" => 487155
                ],
            ],
            "brand" => [
                "value" => "New Balance",
                "id" => 301365
            ],
            "modification" => [
                "value" => "Catalog",
                "id" => 487155
            ],
        ],
    ]);*/

    /*[
		{
			"title": {
				"ru": "Название варианта товара на русском",
				"uk": "Название варианта товара на украинском",
				"en": "Название варианта товара на английском"
			},
			"parent_title": {
				"ru": "Название товара на русском",
				"uk": "Название товара на украинском",
				"en": "Название товара на английском"
			},
			"description": {
				"ru": "Описание товара на русском",
				"uk": "Описание товара на украинском",
				"en": "Описание товара на английском"
			},
			"unit": {
				"ru": "Единицы измерения на русском",
				"uk": "Единицы измерения на украинском",
				"en": "Единицы измерения на английском"
			},
			"seo_title": {
				"ru": "seo_title товара на русском",
				"uk": "seo_title товара на украинском",
				"en": "seo_title товара на английском"
			},
			"seo_keywords": {
				"ru": "seo_keywords товара на русском",
				"uk": "seo_keywords товара на украинском",
				"en": "seo_keywords товара на английском"
			},
			"seo_description": {
				"ru": "seo_description товара на русском",
				"uk": "seo_description товара на украинском",
				"en": "seo_description товара на английском"
			},
			"sku" : "product_variant_sku",
			"parent_sku" : "product_sku",
			"currency" : "UAH",
			"price" : "123.15",
			"price_old" : "152.15",
			"wholesale_price" : "123.15",
			"wholesale_count" : 123,
			"presence" : "true",
			"force_alias_update" : "1",
			"residues" : 125,
			"characteristics":{
				"ID_4884" : [
					{
						"id" : 112143,
						"value" : {
							"ru": "1174 g",
                            "uk": "1174 g",
                            "en": "1174 g"
						}
					}
				],
				"ID_4886": [
                    {
                        "id": 118093,
                        "value": {
                            "ru": "grape",
                            "uk": "grape",
                            "en": "grape"
                        }
                    }
                ]
			},
			"modification" : {
				"id" : 4121,
				"value" : {
					"ru": "Название модификации на русском",
					"uk": "Название модификации на украинском",
					"en": "Название модификации на английском"
				}
			}
		},
		{
			"title": {
				"ru": "Название варианта товара на русском_second",
				"uk": "Название варианта товара на украинском_second",
				"en": "Название варианта товара на английском_second"
			},
            "description": [
                {
                    "name": {
                        "ru": "Вкладка 1",
                        "uk": "Вкладка 1",
                        "en": "Tab 1"
                    },
                    "description": {
                        "ru": "Описание 1",
                        "uk": "Опис 1",
                        "en": "Description 1"
                    }
                },
                {
                    "name": {
                        "ru": "Вкладка 2",
                        "uk": "Вкладка 2",
                        "en": "Tab 2"
                    },
                    "description": {
                        "ru": "Описание 2",
                        "uk": "Опис 2",
                        "en": "Description 2"
                    }
                }
            ]
			"parent_title": {
				"ru": "Название товара на русском",
				"uk": "Название товара на украинском",
				"en": "Название товара на английском"
			},
			"sku" : "product_variant_sku_second",
			"parent_sku" : "product_sku",
			"currency" : "UAH",
			"price" : "123.15",
			"price_old" : "152.15",
			"wholesale_price" : "123.15",
			"wholesale_count" : 123,
			"display_in_showcase" : "true",
			"presence" : "true",
			"force_alias_update" : "0",
			"residues" : 125,
            "characteristics": [
                {
                    "external_id": "431",
                    "value": {
                        "ru": "Размер"
                    },
                    "values": [
                        {
                            "value": {
                                "ru": "40",
                                "uk": "40"
                            }
                        }
                    ]
                },
                {
                    "external_id": "5431",
                    "value": {
                        "ru": "Сезон"
                    },
                    "values": [
                        {
                            "value": {
                                "ru": "Зимові",
                                "uk": "Зимние"
                            }
                        }
                    ]
                }
            ],
			"parent" : [
				{
					"id" : 14123,
					"value" : {
						"ru": "Название категории на русском",
						"uk": "Название категории на украинском",
						"en": "Название категории на английском"
					}
				},
				{
					"id" : 14124,
					"parent_id" : 14123,
					"value" : {
						"ru": "Название категории на русском",
						"uk": "Название категории на украинском",
						"en": "Название категории на английском"
					}
				}
			]
		}
	]*/


    $products = $product->createShopProducts([
        [
            "title" => [
                "ru" => "Название варианта товара на русском",
                "uk" => "Название варианта товара на украинском",
                "en" => "Название варианта товара на английском"
            ],
            "parent_title" => [
                "ru" => "Название товара на русском",
                "uk" => "Название товара на украинском",
                "en" => "Название товара на английском"
            ],
            "description" => [
                "ru" => "Описание товара на русском",
                "uk" => "Описание товара на украинском",
                "en" => "Описание товара на английском"
            ],
            "unit" => [
                "ru" => "Единицы измерения на русском",
                "uk" => "Единицы измерения на украинском",
                "en" => "Единицы измерения на английском"
            ],
            "seo_title" => [
                "ru" => "seo_title товара на русском",
                "uk" => "seo_title товара на украинском",
                "en" => "seo_title товара на английском"
            ],
            "seo_keywords" => [
                "ru" => "seo_keywords товара на русском",
                "uk" => "seo_keywords товара на украинском",
                "en" => "seo_keywords товара на английском"
            ],
            "seo_description" => [
                "ru" => "seo_description товара на русском",
                "uk" => "seo_description товара на украинском",
                "en" => "seo_description товара на английском"
            ],
            "sku" => "product_variant_sku",
            "parent_sku" => "product_sku",
            "currency" => "UAH",
            "price" => 999,
            "price_old" => 9999,
            "wholesale_price" => 999,
            "wholesale_count" => 123,
            "presence" => "true",
            "force_alias_update" => "1",
            "residues" => 125,
            "characteristics" => [
                "ID_4884" => [
                    [
                        "id" => 112143,
                        "value" => [
                            "ru" => "1174 g",
                            "uk" => "1174 g",
                            "en" => "1174 g"
                        ]
                    ]
                ],
                "ID_4886" => [
                    [
                        "id" => 118093,
                        "value" => [
                            "ru" => "grape",
                            "uk" => "grape",
                            "en" => "grape"
                        ]
                    ]
                ]
            ],
            "modification" => [
                "id" => 4121,
                "value" => [
                    "ru" => "Название модификации на русском",
                    "uk" => "Название модификации на украинском",
                    "en" => "Название модификации на английском"
                ]
            ]
        ],
        [
            "title" => [
                "ru" => "Название варианта товара на русском 2",
                "uk" => "Название варианта товара на украинском 2",
                "en" => "Название варианта товара на английском 2"
            ],
            "parent_title" => [
                "ru" => "Название товара на русском",
                "uk" => "Название товара на украинском",
                "en" => "Название товара на английском"
            ],
            "description" => [
                "ru" => "Описание товара на русском",
                "uk" => "Описание товара на украинском",
                "en" => "Описание товара на английском"
            ],
            "unit" => [
                "ru" => "Единицы измерения на русском",
                "uk" => "Единицы измерения на украинском",
                "en" => "Единицы измерения на английском"
            ],
            "seo_title" => [
                "ru" => "seo_title товара на русском",
                "uk" => "seo_title товара на украинском",
                "en" => "seo_title товара на английском"
            ],
            "seo_keywords" => [
                "ru" => "seo_keywords товара на русском",
                "uk" => "seo_keywords товара на украинском",
                "en" => "seo_keywords товара на английском"
            ],
            "seo_description" => [
                "ru" => "seo_description товара на русском",
                "uk" => "seo_description товара на украинском",
                "en" => "seo_description товара на английском"
            ],
            "sku" => "product_variant_sku_2",
            "parent_sku" => "product_sku",
            "currency" => "UAH",
            "price" => "1230.15",
            "price_old" => "1520.15",
            "wholesale_price" => "1230.15",
            "wholesale_count" => 123,
            "presence" => "true",
            "force_alias_update" => "1",
            "residues" => 125,
            "characteristics" => [
                "ID_4884" => [
                    [
                        "id" => 112143,
                        "value" => [
                            "ru" => "1174 g",
                            "uk" => "1174 g",
                            "en" => "1174 g"
                        ]
                    ]
                ],
                "ID_4886" => [
                    [
                        "id" => 118093,
                        "value" => [
                            "ru" => "grape",
                            "uk" => "grape",
                            "en" => "grape"
                        ]
                    ]
                ]
            ],
            "modification" => [
                "id" => 4121,
                "value" => [
                    "ru" => "Название модификации на русском",
                    "uk" => "Название модификации на украинском",
                    "en" => "Название модификации на английском"
                ]
            ]
        ],
        [
            "title" => [
                "ru" => "Название варианта товара на русском 3",
                "uk" => "Название варианта товара на украинском 3",
                "en" => "Название варианта товара на английском 3"
            ],
            "parent_title" => [
                "ru" => "Название товара на русском",
                "uk" => "Название товара на украинском",
                "en" => "Название товара на английском"
            ],
            "description" => [
                "ru" => "Описание товара на русском",
                "uk" => "Описание товара на украинском",
                "en" => "Описание товара на английском"
            ],
            "unit" => [
                "ru" => "Единицы измерения на русском",
                "uk" => "Единицы измерения на украинском",
                "en" => "Единицы измерения на английском"
            ],
            "seo_title" => [
                "ru" => "seo_title товара на русском",
                "uk" => "seo_title товара на украинском",
                "en" => "seo_title товара на английском"
            ],
            "seo_keywords" => [
                "ru" => "seo_keywords товара на русском",
                "uk" => "seo_keywords товара на украинском",
                "en" => "seo_keywords товара на английском"
            ],
            "seo_description" => [
                "ru" => "seo_description товара на русском",
                "uk" => "seo_description товара на украинском",
                "en" => "seo_description товара на английском"
            ],
            "sku" => "product_variant_sku_3",
            "parent_sku" => "product_sku",
            "currency" => "UAH",
            "price" => "12300.15",
            "price_old" => "15200.15",
            "wholesale_price" => "12300.15",
            "wholesale_count" => 123,
            "presence" => "true",
            "force_alias_update" => "1",
            "residues" => 125,
            "characteristics" => [
                "ID_4884" => [
                    [
                        "id" => 112143,
                        "value" => [
                            "ru" => "1174 g",
                            "uk" => "1174 g",
                            "en" => "1174 g"
                        ]
                    ]
                ],
                "ID_4886" => [
                    [
                        "id" => 118093,
                        "value" => [
                            "ru" => "grape",
                            "uk" => "grape",
                            "en" => "grape"
                        ]
                    ]
                ]
            ],
            "modification" => [
                "id" => 4121,
                "value" => [
                    "ru" => "Название модификации на русском",
                    "uk" => "Название модификации на украинском",
                    "en" => "Название модификации на английском"
                ]
            ]
        ],
    ]);


    dd($products);
});
