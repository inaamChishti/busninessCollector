<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    public function log(string $action, string $description, mixed $subject = null, array $metadata = []): void
    {
        ActivityLog::create(['user_id'=>auth()->id(),'action'=>$action,'description'=>$description,'subject_type'=>is_object($subject)?$subject::class:null,'subject_id'=>is_object($subject)?$subject->getKey():null,'metadata'=>$metadata ?: null,'ip_address'=>request()?->ip()]);
    }
}
