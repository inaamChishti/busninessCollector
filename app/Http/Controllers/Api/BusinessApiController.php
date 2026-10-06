<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;

class BusinessApiController extends Controller
{
    public function index(Request$r){return Business::query()->when($r->filled('updated_since'),fn($q)=>$q->where('updated_at','>=',$r->date('updated_since')))->paginate(min(100,max(1,$r->integer('per_page',50))));}
    public function show(Business$business){return$business->load(['emails','sources']);}
    public function emails(Business$business){return$business->emails()->paginate(100);}
}
