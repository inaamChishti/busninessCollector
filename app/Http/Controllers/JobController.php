<?php

namespace App\Http\Controllers;

use App\Models\CollectionJob;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function index(){return view('jobs.index',['tracked'=>CollectionJob::latest()->paginate(40),'queueCounts'=>DB::table('jobs')->select('queue',DB::raw('COUNT(*) total'))->groupBy('queue')->pluck('total','queue')]);}
}
