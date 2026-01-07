<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use DB;

class AuthController extends Controller
{



function form(Request $request){
    dd($request->all());
}

    public function AuthIndex()
    {
        return view('Auth.login');
    }

public function AuthLogin(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    $user = DB::table('admin')
        ->where('email', $credentials['email'])
        ->where('password', $credentials['password']) 
        ->first();
    if ($user) {
        session(['user' => $user]);
        session(['user_id' => $user->id]); 
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }
    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
}

public function logout(Request $request)
{

    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
}



}
