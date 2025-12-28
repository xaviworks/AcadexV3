<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Scheduled Backup Tasks
|--------------------------------------------------------------------------
|
| Automatic backups based on system settings.
| To enable, set up a cron job: * * * * * php artisan schedule:run
|
*/

if (Schema::hasTable('settings')) {
    try {
        $scheduleSetting = Setting::where('key', 'backup_schedule')->value('value');
        
        if ($scheduleSetting) {
            $config = json_decode($scheduleSetting, true);
            $frequency = $config['frequency'] ?? 'never';
            $time = $config['time'] ?? '00:00';

            if ($frequency !== 'never') {
                $command = Schedule::command('backup:run --type=full')
                    ->appendOutputTo(storage_path('logs/backup.log'))
                    ->description('Scheduled automatic database backup');

                match ($frequency) {
                    'daily' => $command->dailyAt($time),
                    'weekly' => $command->weekly()->at($time),
                    'monthly' => $command->monthly()->at($time),
                    default => null,
                };
            }
        }
    } catch (\Throwable $e) {
        // Fail silently if DB connection fails during schedule run
    }
}
