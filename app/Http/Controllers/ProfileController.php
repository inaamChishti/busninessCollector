<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request$r){return view('profile.edit',['user'=>$r->user()]);}
    public function update(Request$r){$u=$r->user();$data=$r->validate(['name'=>'required|string|max:100','email'=>['required','email','max:190',Rule::unique('users')->ignore($u)],'current_password'=>'nullable|required_with:password|current_password','password'=>'nullable|string|min:12|confirmed']);$u->name=$data['name'];$u->email=$data['email'];if(filled($data['password']??null))$u->password=$data['password'];$u->save();return back()->with('success','Profile updated.');}
}
