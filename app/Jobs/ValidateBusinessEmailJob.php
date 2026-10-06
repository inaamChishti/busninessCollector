<?php

namespace App\Jobs;

use App\Models\BusinessEmail;
use App\Services\EmailExtractionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ValidateBusinessEmailJob implements ShouldQueue
{
    use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;
    public function __construct(public readonly int $emailId){$this->onQueue('validation');}
    public function handle(EmailExtractionService $validator):void{$email=BusinessEmail::find($this->emailId);if(!$email)return;$email->update(['validation_status'=>$validator->valid($email->email)?'domain_valid':'invalid','last_seen_at'=>now()]);}
}
