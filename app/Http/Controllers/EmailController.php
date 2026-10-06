<?php

namespace App\Http\Controllers;

use App\Models\BusinessEmail;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index(Request$r){$q=BusinessEmail::with('business');if($r->filled('q'))$q->where('email','like','%'.$r->q.'%');if($r->filled('validation_status'))$q->where('validation_status',$r->validation_status);return view('emails.index',['emails'=>$q->latest()->paginate(50)->withQueryString()]);}
    public function primary(BusinessEmail$email){$email->business->emails()->update(['is_primary'=>false]);$email->update(['is_primary'=>true]);$email->business->update(['primary_email'=>$email->email]);return back()->with('success','Primary email updated.');}
    public function destroy(BusinessEmail$email){$business=$email->business;$wasPrimary=$email->is_primary;$email->delete();if($wasPrimary){$next=$business->emails()->first();$next?->update(['is_primary'=>true]);$business->update(['primary_email'=>$next?->email,'email_status'=>$next?'email_found':'website_found_no_email']);}return back()->with('success','Email removed.');}
}
