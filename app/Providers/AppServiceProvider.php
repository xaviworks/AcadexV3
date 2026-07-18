<?php

namespace App\Providers;

use App\Support\Database\PublicUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
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
        Event::listen('eloquent.creating: *', function (string $eventName, array $payload): void {
            if (($payload[0] ?? null) instanceof Model) {
                PublicUuid::fill($payload[0]);
            }
        });

        DB::whenQueryingForLongerThan((int) config('database.slow_query_ms'), function ($connection, QueryExecuted $event): void {
            Log::warning('Slow database query detected.', [
                'connection' => $connection->getName(),
                'time_ms' => $event->time,
                'sql' => $event->toRawSql(),
            ]);
        });

        // Register Brevo HTTP API mail transport (port 443, bypasses Railway SMTP block)
        Mail::extend('brevo', function (array $config) {
            $factory = new \Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory();

            return $factory->create(
                new Dsn('brevo+api', 'default', $config['key'] ?? config('services.brevo.key'))
            );
        });
    }
}
