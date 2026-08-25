<?php

use App\Providers\AppServiceProvider;
use App\Providers\RateLimitServiceProvider;
use App\Providers\RepositoryServiceProvider;

return [
    AppServiceProvider::class,
    RepositoryServiceProvider::class,
    RateLimitServiceProvider::class,
];
