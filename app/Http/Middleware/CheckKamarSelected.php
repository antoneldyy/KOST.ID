<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kamar;

class CheckKamarSelected
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // cek apakah user sudah punya kamar
        $kamar = Kamar::where('id', $user->id)->first();

        if (!$kamar) {
            return redirect()->route('pilih.kamar')->with('warning', 'Silakan pilih kamar terlebih dahulu.');
        }

        return $next($request);
    }
}
