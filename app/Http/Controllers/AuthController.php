<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Auth::check() ? redirect('/spks') : view('auth.login');
    }

    public function login(Request $request)
    {
        $creds = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($creds, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/spks');
        }

        return back()->withErrors(['username' => 'Username atau password salah.'])
        ->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
