<?php

namespace App\Http\Controllers;

use App\Jobs\ExtractBusinessEmailsJob;
use App\Jobs\FindBusinessWebsiteJob;
use App\Jobs\VerifyBusinessWebsiteJob;
use App\Models\Business;
use App\Models\BusinessWebsite;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function index(Request$r){$q=Business::query();foreach(['city','postcode','primary_category','external_provider','email_status','website_status']as$f)if($r->filled($f))$q->where($f,$r->$f);if($r->has('has_email'))$r->has_email==='1'?$q->whereNotNull('primary_email'):$q->whereNull('primary_email');if($r->has('has_website'))$r->has_website==='1'?$q->whereNotNull('official_website'):$q->whereNull('official_website');if($r->filled('q'))$q->where(fn($x)=>$x->where('business_name','like','%'.$r->q.'%')->orWhere('phone','like','%'.$r->q.'%')->orWhere('postcode','like','%'.$r->q.'%'));return view('businesses.index',['businesses'=>$q->latest('discovered_at')->paginate(50)->withQueryString()]);}
    public function create(){return view('businesses.create');}
    public function store(Request$r,ActivityLogger$a){$data=$this->validated($r);$data+=['website_status'=>'pending','email_status'=>'pending','website_origin'=>'manual','discovered_at'=>now()];$b=Business::create($data);$a->log('business.created','Manually created '.$b->business_name,$b);return redirect()->route('businesses.show',$b)->with('success','Business created.');}
    public function show(Business$business){$business->load(['emails','websites','sources','crawlPages'=>fn($q)=>$q->latest()]);return view('businesses.show',compact('business'));}
    public function edit(Business$business){return view('businesses.edit',compact('business'));}
    public function update(Request$r,Business$business,ActivityLogger$a){$business->update($this->validated($r));$a->log('business.updated','Updated '.$business->business_name,$business);return redirect()->route('businesses.show',$business)->with('success','Business updated.');}
    public function destroy(Business$business){$business->delete();return redirect()->route('businesses.index')->with('success','Business deleted.');}
    public function findWebsite(Business$b){FindBusinessWebsiteJob::dispatch($b->id);$b->update(['website_status'=>'queued']);return back()->with('success','Website search queued.');}
    public function findEmail(Business$b){abort_if(!$b->official_website,422,'No official website is set.');ExtractBusinessEmailsJob::dispatch($b->id);$b->update(['email_status'=>'queued']);return back()->with('success','Email search queued.');}
    public function verify(Business$b,BusinessWebsite$website){abort_unless($website->business_id===$b->id,404);VerifyBusinessWebsiteJob::dispatch($b->id,$website->id);return back()->with('success','Website verification queued.');}
    public function approveWebsite(Business$b,BusinessWebsite$website){abort_unless($website->business_id===$b->id,404);$website->update(['status'=>'approved']);$b->update(['official_website'=>$website->url,'website_domain'=>$website->domain,'website_confidence_score'=>$website->confidence_score,'website_verification_evidence'=>$website->verification_evidence,'website_manually_verified'=>true,'website_origin'=>'manually_verified','website_status'=>'verified','website_checked_at'=>now()]);return back()->with('success','Website approved and protected from automatic replacement.');}
    public function rejectWebsite(Business$b,BusinessWebsite$website){abort_unless($website->business_id===$b->id,404);$website->update(['status'=>'rejected']);if($b->official_website===$website->url)$b->update(['official_website'=>null,'website_domain'=>null,'website_manually_verified'=>false,'website_status'=>'rejected']);return back()->with('success','Website rejected.');}
    private function validated(Request$r):array{return$r->validate(['business_name'=>'required|string|max:255','address_line_1'=>'nullable|string|max:255','address_line_2'=>'nullable|string|max:255','city'=>'nullable|string|max:120','region'=>'nullable|string|max:120','postcode'=>'nullable|string|max:24','country'=>'required|string|size:2','phone'=>'nullable|string|max:64','primary_category'=>'nullable|string|max:255','official_website'=>'nullable|url:http,https|max:2048']);}
}
