<?php

namespace App\Http\Controllers;

use App\Jobs\ImportLocationsJob;
use App\Models\ImportRecord;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function index(){return view('imports.index',['imports'=>ImportRecord::latest()->paginate(25)]);}
    public function store(Request$request,ActivityLogger$activity){$request->validate(['csv'=>['required','file','mimes:csv,txt','max:102400']]);$file=$request->file('csv');$handle=fopen($file->getRealPath(),'rb');$header=fgetcsv($handle);fclose($handle);$names=array_map(fn($v)=>strtolower(trim((string)$v)),$header?:[]);if(!in_array('postcode',$names,true)||!in_array('city',$names,true))return back()->withErrors(['csv'=>'CSV must contain postcode and city headers.']);$import=ImportRecord::create(['user_id'=>$request->user()->id,'filename'=>$file->getClientOriginalName(),'status'=>'queued']);$path=$file->storeAs('imports',$import->id.'-'.preg_replace('/[^a-zA-Z0-9._-]/','_',$file->getClientOriginalName()),'local');ImportLocationsJob::dispatch($import->id,$path);$activity->log('locations.imported','Location CSV queued: '.$import->filename,$import);return back()->with('success','CSV validated and queued for import.');}
}
