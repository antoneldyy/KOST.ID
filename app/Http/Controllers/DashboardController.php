<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\User;
use App\Models\Payment;
use App\Models\Activity;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(Request $request) {
        $month = (int)($request->get('month', now()->month));
        $year = (int)($request->get('year', now()->year));

        $totalRooms = Room::count();
        $occupiedRooms = Room::whereNotNull('user_id')->count();
        $emptyRooms = $totalRooms - $occupiedRooms;

        // Only count approved payments toward revenue when status column exists, else fallback to paid_at
        $paymentsQuery = Payment::where('month', $month)->where('year', $year);
        if (\Schema::hasColumn('payments', 'status')) {
            $paymentsQuery->where('status', 'approved');
        } else {
            $paymentsQuery->whereNotNull('approved_at');
        }
        $monthlyRevenue = $paymentsQuery->sum('amount');

        $paidCount = Payment::where('month', $month)->where('year', $year)->whereNotNull('paid_at')->count();
        $unpaidCount = Payment::where('month', $month)->where('year', $year)->whereNull('paid_at')->count();
        
        // Get recent activities for the current month
        $recentActivities = Activity::with('user')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard', compact('month','year','totalRooms','occupiedRooms','emptyRooms','monthlyRevenue','paidCount','unpaidCount','recentActivities'));
    }

    public function export(Request $request): StreamedResponse
    {
        $month = (int)($request->get('month', now()->month));
        $year = (int)($request->get('year', now()->year));

        $filename = "laporan-{$year}-" . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($month, $year) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nomor Kamar', 'Nama Penghuni', 'Bulan', 'Tahun', 'Jumlah', 'Tanggal Bayar', 'Status']);
            $payments = Payment::with(['user','room'])->where('month', $month)->where('year', $year)->get();
            foreach ($payments as $payment) {
                fputcsv($handle, [
                    optional($payment->room)->number,
                    optional($payment->user)->name,
                    $payment->month,
                    $payment->year,
                    $payment->amount,
                    optional($payment->paid_at)?->format('Y-m-d'),
                    $payment->paid_at ? 'Lunas' : 'Belum Bayar',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
