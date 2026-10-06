<?php

namespace App\Console;

use App\Jobs\ExtractBusinessEmailsJob;
use App\Jobs\FindBusinessWebsiteJob;
use App\Models\Business;
use App\Models\Setting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        for ($slot = 1; $slot <= 5; $slot++) {
            $schedule->command("collector:work-slot {$slot}")
                ->name("collector-worker-{$slot}")
                ->everyMinute()
                ->withoutOverlapping(3)
                ->runInBackground();
        }

        $schedule->command('queue:prune-batches --hours=48 --unfinished=72 --cancelled=72')->daily();
        $schedule->command('queue:prune-failed --hours=168')->daily();

        $schedule->call(function () {
            if (Setting::valueOf('collection_paused', true)) return;

            $websiteDays = (int) Setting::valueOf('recheck_websites_after_days', 90);
            Business::whereNull('official_website')
                ->where(fn($q) => $q->whereNull('website_checked_at')
                    ->orWhere('website_checked_at', '<', now()->subDays($websiteDays)))
                ->limit(200)->pluck('id')
                ->each(fn($id) => FindBusinessWebsiteJob::dispatch($id));

            $emailDays = (int) Setting::valueOf('recheck_no_email_after_days', 30);
            Business::whereNotNull('official_website')
                ->whereNull('primary_email')
                ->where(fn($q) => $q->whereNull('email_checked_at')
                    ->orWhere('email_checked_at', '<', now()->subDays($emailDays)))
                ->limit(200)->pluck('id')
                ->each(fn($id) => ExtractBusinessEmailsJob::dispatch($id));
        })->name('dispatch-due-business-rechecks')->dailyAt('02:10')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
