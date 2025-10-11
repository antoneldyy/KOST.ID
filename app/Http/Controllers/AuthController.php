<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    public function showRegister() {
        return view('auth.register');
    }

    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'occupation' => 'required|string|max:255',
            'address' => 'required|string',
            'ktp' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Handle KTP file upload
        $ktpPath = null;
        if ($request->hasFile('ktp')) {
            $ktpPath = $request->file('ktp')->store('ktp', 'public');
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'occupation' => $request->occupation,
            'address' => $request->address,
            'ktp_path' => $ktpPath,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'status' => 'active',
        ]);

        return redirect('/login')->with('success', 'Registrasi berhasil! Silakan login dengan akun Anda.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'Email tidak valid'
        ]);

        // Generate reset token
        $token = Str::random(64);
        
        // Store token in password_resets table
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => $token,
                'created_at' => now()
            ]
        );

        // Redirect langsung ke halaman reset password
        return redirect()->route('password.reset', [
            'token' => $token,
            'email' => $request->email
        ])->with('success', 'Email ditemukan. Silakan buat password baru Anda.');
    }

    public function showResetPassword(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email) {
            return redirect('/forgot-password')->with('error', 'Link reset password tidak valid.');
        }

        // Verify token
        $passwordReset = DB::table('password_resets')
            ->where('email', $email)
            ->where('token', $token)
            ->where('created_at', '>', now()->subHours(1)) // Token valid for 1 hour
            ->first();

        if (!$passwordReset) {
            return redirect('/forgot-password')->with('error', 'Link reset password tidak valid atau sudah expired.');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Verify token
        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->where('created_at', '>', now()->subHours(1)) // Token valid for 1 hour
            ->first();

        if (!$passwordReset) {
            return back()->withErrors(['email' => 'Token tidak valid atau sudah expired.']);
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Delete the reset token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect('/login')->with('success', 'Password berhasil direset! Silakan login dengan password baru.');
    }
}
