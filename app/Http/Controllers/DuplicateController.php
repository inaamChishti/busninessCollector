<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\DuplicateCandidate;
use Illuminate\Support\Facades\DB;

class DuplicateController extends Controller
{
    public function index(){return view('duplicates.index',['duplicates'=>DuplicateCandidate::where('status','pending')->with(['business','possibleDuplicate'])->paginate(30)]);}
    public function dismiss(DuplicateCandidate$duplicate){$duplicate->update(['status'=>'not_duplicate']);return back()->with('success','Marked as not a duplicate.');}
    public function merge(DuplicateCandidate$duplicate){DB::transaction(function()use($duplicate){$keep=Business::findOrFail($duplicate->possible_duplicate_id);$remove=Business::findOrFail($duplicate->business_id);foreach($remove->emails as$email)$keep->emails()->updateOrCreate(['email'=>$email->email],$email->only(['email_type','source_url','is_primary','is_generic','validation_status','first_seen_at','last_seen_at']));foreach($remove->locations as$location)$keep->locations()->syncWithoutDetaching([$location->id=>['searched_postcode'=>$location->pivot->searched_postcode,'business_category_id'=>$location->pivot->business_category_id]]);foreach(['phone','official_website','website_domain','primary_email','address_line_1','address_line_2','city','region','postcode']as$f)if(blank($keep->$f)&&filled($remove->$f))$keep->$f=$remove->$f;$keep->save();$duplicate->update(['status'=>'merged']);$remove->delete();});return back()->with('success','Businesses merged.');}
}
