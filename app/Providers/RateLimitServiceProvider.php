<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(config('ratelimit.api_per_minute'))
                ->by($request->user()?->id ?? $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $username = Str::lower((string) $request->input('username'));

            return [
                Limit::perMinute(config('ratelimit.login_per_minute'))
                    ->by($username.'|'.$request->ip()),
                Limit::perMinute(config('ratelimit.login_per_minute_by_ip'))
                    ->by('login-ip|'.$request->ip()),
            ];
        });

        RateLimiter::for('orders', function (Request $request) {
            return Limit::perMinute(config('ratelimit.orders_per_minute'))
                ->by($request->ip());
        });
    }
}
