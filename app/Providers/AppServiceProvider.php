<?php

namespace App\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Transport\Dsn;

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
        // Register Brevo HTTP API mail transport (port 443, bypasses Railway SMTP block)
        Mail::extend('brevo', function (array $config) {
            $factory = new \Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory();

            return $factory->create(
                new Dsn('brevo+api', 'default', $config['key'] ?? config('services.brevo.key'))
            );
        });
    }
}
