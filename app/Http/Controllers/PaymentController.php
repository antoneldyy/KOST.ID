<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Models\Room;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'nullable|exists:rooms,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'amount' => 'required|integer|min:0',
            'paid_at' => 'nullable|date',
        ]);
        Payment::updateOrCreate(
            ['user_id' => $validated['user_id'], 'month' => $validated['month'], 'year' => $validated['year']],
            $validated
        );
        return back()->with('success', 'Pembayaran disimpan');
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'amount' => 'sometimes|integer|min:0',
            'paid_at' => 'nullable|date',
        ]);
        $payment->update($validated);
        return back()->with('success', 'Pembayaran diperbarui');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return back()->with('success', 'Pembayaran dihapus');
    }

    public function approve(Payment $payment)
    {
        $payment->update([
            'paid_at' => $payment->paid_at ?? now()->toDateString(),
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        // Create notification for user
        Notification::create([
            'type' => 'payment_approved',
            'title' => 'Pembayaran Disetujui',
            'message' => "Pembayaran untuk kamar {$payment->room->number} bulan {$payment->month}/{$payment->year} telah disetujui.",
            'data' => [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'room_id' => $payment->room_id
            ]
        ]);

        \App\Models\Activity::create([
            'user_id' => auth()->id(),
            'action' => 'approve_payment',
            'meta' => ['payment_id' => $payment->id, 'user_id' => $payment->user_id, 'month' => $payment->month, 'year' => $payment->year],
        ]);

        return response()->json(['success' => true, 'message' => 'Pembayaran disetujui']);
    }

    public function reject(Payment $payment)
    {
        // Create notification for user
        Notification::create([
            'type' => 'payment_rejected',
            'title' => 'Pembayaran Ditolak',
            'message' => "Pembayaran untuk kamar {$payment->room->number} bulan {$payment->month}/{$payment->year} telah ditolak. Silakan upload bukti pembayaran yang valid.",
            'data' => [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'room_id' => $payment->room_id
            ]
        ]);

        \App\Models\Activity::create([
            'user_id' => auth()->id(),
            'action' => 'reject_payment',
            'meta' => ['payment_id' => $payment->id, 'user_id' => $payment->user_id, 'month' => $payment->month, 'year' => $payment->year],
        ]);

        return response()->json(['success' => true, 'message' => 'Pembayaran ditolak']);
    }
}


