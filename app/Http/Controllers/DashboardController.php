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

        // Calculate monthly revenue from approved payments
        $paymentsQuery = Payment::where('month', $month)->where('year', $year);
        if (\Schema::hasColumn('payments', 'status')) {
            $paymentsQuery->where('status', 'approved');
        } else {
            $paymentsQuery->whereNotNull('approved_at');
        }
        $monthlyRevenue = $paymentsQuery->sum('amount');

        // Calculate payment status for all rooms in the selected month
        $totalRoomsForMonth = $totalRooms; // All rooms should be considered for payment status
        $paidCount = Payment::where('month', $month)
            ->where('year', $year)
            ->where(function($query) {
                if (\Schema::hasColumn('payments', 'status')) {
                    $query->where('status', 'approved');
                } else {
                    $query->whereNotNull('approved_at');
                }
            })
            ->count();
        $unpaidCount = $totalRoomsForMonth - $paidCount;
        
        // Get recent activities for the current month (all users, not just admin)
        $recentActivities = Activity::with(['user.room'])
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
