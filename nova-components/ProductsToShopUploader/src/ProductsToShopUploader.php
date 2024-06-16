<?php

namespace Berika\ProductsToShopUploader;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class ProductsToShopUploader extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('products-to-shop-uploader', __DIR__.'/../dist/js/tool.js');
        Nova::style('products-to-shop-uploader', __DIR__.'/../dist/css/tool.css');
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     */
    public function menu(Request $request)
    {
        return MenuSection::make('Products To Shop Uploader')
            ->path('/products-to-shop-uploader')
            ->icon('server');
    }
}
