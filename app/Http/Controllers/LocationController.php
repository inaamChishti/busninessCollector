<?php

namespace App\Http\Controllers;

use App\Models\BusinessSearch;
use App\Models\Location;
use App\Services\CollectionManager;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request$r){$q=Location::query();if($r->filled('status'))$q->where('status',$r->status);if($r->filled('q'))$q->where(fn($x)=>$x->where('postcode','like','%'.$r->q.'%')->orWhere('city','like','%'.$r->q.'%'));return view('locations.index',['locations'=>$q->latest()->paginate(50)->withQueryString()]);}
    public function bulk(Request$r,CollectionManager$m){$data=$r->validate(['ids'=>'required|array','ids.*'=>'integer|exists:locations,id','action'=>'required|in:process,reprocess,delete']);$ids=$data['ids'];if($data['action']==='delete'){Location::whereIn('id',$ids)->delete();return back()->with('success','Selected locations deleted.');}if($data['action']==='reprocess'){BusinessSearch::whereIn('location_id',$ids)->delete();Location::whereIn('id',$ids)->update(['status'=>'pending','completed_at'=>null,'last_error'=>null]);}$count=$m->start($ids);return back()->with('success',"Queued {$count} locations.");}
    public function destroy(Location$location){$location->delete();return back()->with('success','Location deleted.');}
}
