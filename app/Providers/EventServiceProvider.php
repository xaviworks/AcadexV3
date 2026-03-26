<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The framework-level event provider already auto-discovers listeners
     * in app/Listeners, so keeping manual auth listener mappings here causes
     * duplicate registrations and duplicate user log entries.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [];

    public function boot(): void
    {
        //
    }
}
