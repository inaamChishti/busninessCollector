<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SetupController extends Controller
{
    private function allowed(Request$request):bool{return User::count()===0&&filled(config('app.installer_token',env('INSTALLER_TOKEN')))&&hash_equals((string)env('INSTALLER_TOKEN'),(string)$request->query('token',$request->input('token')));}
    public function create(Request$request){abort_unless($this->allowed($request),404);return view('setup.create',['token'=>$request->query('token')]);}
    public function store(Request$request){abort_unless($this->allowed($request),404);$data=$request->validate(['name'=>'required|string|max:100','email'=>'required|email|max:190|unique:users','password'=>'required|string|min:12|confirmed','token'=>'required|string']);User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>$data['password'],'is_admin'=>true]);return redirect()->route('login')->with('success','Administrator created. You can now sign in.');}
}
