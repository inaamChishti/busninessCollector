<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class FailedJobController extends Controller
{
    public function index(){return view('failed-jobs.index',['jobs'=>DB::table('failed_jobs')->latest('failed_at')->paginate(30)]);}
    public function retry(Request$r){$ids=$r->validate(['ids'=>'required|array','ids.*'=>'string'])['ids'];foreach($ids as$id)Artisan::call('queue:retry',['id'=>[$id]]);return back()->with('success','Selected jobs queued for retry.');}
    public function retryAll(){Artisan::call('queue:retry',['id'=>['all']]);return back()->with('success','All failed jobs queued for retry.');}
    public function destroy(string$uuid){DB::table('failed_jobs')->where('uuid',$uuid)->delete();return back()->with('success','Failed job ignored and removed.');}
}
