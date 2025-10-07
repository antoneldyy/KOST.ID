<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email|max:50',
            'password' => 'required|max:50',
        ]);

        // validasi pembagian roles
        if(Auth::attempt($request->only('email', 'password'), $request->remember)) {
            if(Auth::user()->role == 'user') return redirect('/user');
            return redirect('/dashboard');
        }
        return back()->with('failed', 'Email atau password salah');
    }

    public function logout() {
        Auth::logout(Auth::user());
        return redirect('/login');
    }
}
