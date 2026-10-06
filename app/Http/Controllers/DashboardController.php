<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Business;
use App\Models\Location;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(){return view('dashboard',['stats'=>[
        'locations'=>Location::count(),'completed_locations'=>Location::where('status','completed')->count(),'remaining_locations'=>Location::whereNot('status','completed')->count(),'failed_locations'=>Location::where('status','failed')->count(),
        'businesses'=>Business::count(),'websites'=>Business::whereNotNull('official_website')->count(),'with_emails'=>Business::whereNotNull('primary_email')->count(),'multiple_emails'=>Business::where('email_status','multiple_emails_found')->count(),'contact_form_only'=>Business::where('email_status','contact_form_only')->count(),'no_email'=>Business::where('email_status','website_found_no_email')->count(),'processing'=>Business::whereIn('email_status',['pending','retry_required'])->count(),'failed'=>Business::whereIn('email_status',['request_failed','crawl_blocked'])->count(),'queue'=>DB::table('jobs')->count()],
        'activities'=>ActivityLog::latest()->limit(12)->get(),'paused'=>(bool)Setting::valueOf('collection_paused',true)]);}
}
