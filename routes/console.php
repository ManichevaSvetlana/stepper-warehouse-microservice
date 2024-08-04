<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('shop:update-shop-data --section=features')->weeklyOn(1, '22:00');

Schedule::command('poizon:update-poizon-shop-data')->dailyAt('09:00');
Schedule::command('sync:systems-products --system=poizon-shop --section=shop --count=10000 --images=0')->dailyAt('02:00');
