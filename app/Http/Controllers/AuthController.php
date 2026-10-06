<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function create(){return view('auth.login');}
    public function store(Request$request){$credentials=$request->validate(['email'=>['required','email'],'password'=>['required','string']]);if(!Auth::attempt($credentials,$request->boolean('remember'))){return back()->withErrors(['email'=>'The supplied credentials are not valid.'])->onlyInput('email');}$request->session()->regenerate();return redirect()->intended(route('dashboard'));}
    public function destroy(Request$request){Auth::logout();$request->session()->invalidate();return redirect()->route('login');}
}
