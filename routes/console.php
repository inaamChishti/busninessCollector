<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;

Artisan::command('collector:work-slot {slot}', function (int $slot) {
    if (Setting::valueOf('collection_paused', true) || $slot > (int) Setting::valueOf('max_concurrent_jobs', 2)) return 0;
    return $this->call('queue:work', [
        'connection'       => 'database',
        '--stop-when-empty'=> true,
        '--max-time'       => 50,
        '--sleep'          => 1,
        '--tries'          => 3,
        '--queue'          => 'business-discovery,website-search,crawler,validation,imports,exports,default',
    ]);
})->purpose('Runs one scheduler-managed collector worker slot.');
