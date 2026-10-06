<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request$r){$q=ActivityLog::query();if($r->filled('action'))$q->where('action','like',$r->action.'%');return view('activity.index',['activities'=>$q->latest()->paginate(50)->withQueryString()]);}
}
