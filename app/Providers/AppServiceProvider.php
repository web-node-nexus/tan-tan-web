<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('otp', function (Request $request) {
            $phone = (string) $request->input('phone', $request->ip());

            return Limit::perMinute(3)->by('otp:'.$phone);
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by('login:'.$request->ip());
        });

        RateLimiter::for('chat', function (Request $request) {
            return Limit::perMinute(60)->by('chat:'.($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('ad-post', function (Request $request) {
            return Limit::perMinute(10)->by('ad-post:'.($request->user()?->id ?: $request->ip()));
        });
    }
}
