<?php

namespace App\Http\Controllers;

use App\Jobs\ExtractBusinessEmailsJob;
use App\Jobs\FindBusinessWebsiteJob;
use App\Models\Business;
use App\Services\CollectionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CollectionController extends Controller
{
    public function start(Request$r,CollectionManager$m){$count=$m->start($r->input('location_ids'));return back()->with('success',"Queued {$count} locations.");}
    public function pause(CollectionManager$m){$m->pause();return back()->with('success','Collection paused.');}
    public function resume(CollectionManager$m){$m->resume();return back()->with('success','Collection resumed.');}
    public function retryFailed(){Artisan::call('queue:retry',['id'=>['all']]);return back()->with('success','Failed queue jobs were returned to the queue.');}
    public function websites(Request$r,CollectionManager$m){$count=$m->findMissingWebsites($r->input('business_ids'));return back()->with('success',"Queued website searches for {$count} businesses.");}
    public function emails(){ $count=0;Business::whereNotNull('official_website')->whereIn('email_status',['pending','website_found_no_email','request_failed','retry_required'])->select('id')->chunkById(200,function($rows)use(&$count){foreach($rows as$row){ExtractBusinessEmailsJob::dispatch($row->id);$count++;}});return back()->with('success',"Queued email searches for {$count} businesses.");}
}
