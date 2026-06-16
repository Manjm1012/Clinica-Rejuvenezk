<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('contact-form', function (Request $request) {
            return Limit::perMinute(6)->by($request->ip());
        });

        RateLimiter::for('api-leads', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('auth-login', function (Request $request) {
            return Limit::perMinute(5)->by(mb_strtolower((string) $request->input('email')) . '|' . $request->ip());
        });

        RateLimiter::for('tayrai-webhook', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        config([
            'livewire.temporary_file_upload.disk' => 'public',
            'livewire.temporary_file_upload.directory' => 'livewire-tmp',
        ]);

        if (! $this->app->runningInConsole() && config('app.env') === 'local') {
            config([
                'app.url' => request()->root(),
            ]);

            URL::forceRootUrl(request()->root());
        }

        if (config('app.env') !== 'local') {
            URL::forceRootUrl(rtrim((string) config('app.url'), '/'));
            URL::forceScheme('https');
        }
    }
}
