<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class UserpageController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil 5 transaksi terakhir user
        $payments = Payment::where('user_id', $user->id)
            ->orderBy('paid_at', 'desc')
            ->take(10)
            ->get();

        // Ambil data untuk grafik (jumlah total pembayaran per bulan)
        $chartData = Payment::selectRaw('month, year, SUM(amount) as total')
            ->where('user_id', $user->id)
            ->groupBy('month', 'year')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Format data untuk chart.js
        $labels = $chartData->map(fn($p) => date('F Y', strtotime("$p->year-$p->month-01")));
        $totals = $chartData->pluck('total');

        return view('userpage', compact('payments', 'labels', 'totals'));
    }
}
