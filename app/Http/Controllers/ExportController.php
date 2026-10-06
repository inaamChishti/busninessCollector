<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateExportJob;
use App\Models\ExportRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function index(){return view('exports.index',['exports'=>ExportRecord::latest()->paginate(25)]);}
    public function store(Request$r){$data=$r->validate(['format'=>'required|in:csv,xlsx','city'=>'nullable|string|max:120','postcode'=>'nullable|string|max:24','has_email'=>'nullable|in:0,1']);$format=$data['format'];unset($data['format']);$export=ExportRecord::create(['user_id'=>$r->user()->id,'format'=>$format,'status'=>'queued','filters'=>array_filter($data,fn($v)=>$v!==null&&$v!=='')]);GenerateExportJob::dispatch($export->id);return back()->with('success','Export queued. It will appear here when ready.');}
    public function download(ExportRecord$export){abort_unless($export->status==='completed'&&$export->filename&&Storage::disk('local')->exists($export->filename),404);return Storage::disk('local')->download($export->filename);}
}
