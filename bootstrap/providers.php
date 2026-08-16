<?php

use App\Infrastructure\Providers\AppServiceProvider;
use App\Infrastructure\Providers\HorizonServiceProvider;
use App\Infrastructure\Providers\TelescopeServiceProvider;

return [
    AppServiceProvider::class,
    HorizonServiceProvider::class,
    TelescopeServiceProvider::class,
];
