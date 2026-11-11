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

        // Portal del paciente: 5 intentos/min por IP y 5/min por identificador (correo o teléfono).
        RateLimiter::for('patient-auth', function (Request $request) {
            $response = fn () => back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['login' => 'Demasiados intentos. Espera un minuto e inténtalo de nuevo.']);

            $identifiers = array_filter([
                $request->input('login'),
                \App\Models\Patient::normalizeEmail($request->input('email')),
                \App\Models\Patient::normalizePhone($request->input('phone')) ?: null,
            ]);

            $limits = [Limit::perMinute(5)->by('patient-auth-ip:' . $request->ip())->response($response)];

            foreach ($identifiers as $identifier) {
                $key = str_contains((string) $identifier, '@')
                    ? \App\Models\Patient::normalizeEmail($identifier)
                    : (\App\Models\Patient::normalizePhone($identifier) ?: mb_strtolower(trim((string) $identifier)));
                $limits[] = Limit::perMinute(5)->by('patient-auth-id:' . sha1((string) $key))->response($response);
            }

            return $limits;
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
