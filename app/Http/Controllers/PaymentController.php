<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Models\Room;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::where('user_id', Auth::id())
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        return view('payment.index', compact('payments'));
    }

    public function uploadProof(Request $request, $id)
    {
        $request->validate([
            'proof' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $payment = Payment::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Do not allow uploading if already approved
        if ($payment->status === 'approved' || $payment->approved_at) {
            return redirect()->route('payment.index')->with('error', 'Pembayaran sudah disetujui, tidak dapat mengunggah ulang.');
        }

        $path = $request->file('proof')->store('proofs', 'public');

        $currentRoomId = optional(Auth::user()->room)->id;

        // Delete old proof if exists
        if ($payment->proof_path && Storage::disk('public')->exists($payment->proof_path)) {
            Storage::disk('public')->delete($payment->proof_path);
        }

        $payment->update([
            'proof_path' => $path,
            'paid_at' => now(),
            'room_id' => $currentRoomId,
            'status' => 'pending',
        ]);

        \App\Models\Activity::create([
            'user_id' => Auth::id(),
            'action' => 'upload_payment_proof',
            'meta' => [
                'payment_id' => $payment->id,
                'month' => $payment->month,
                'year' => $payment->year,
                'room_number' => optional(Auth::user()->room)->number,
            ],
        ]);

        // 🔔 Tambahan notifikasi admin & user
        // Notifikasi untuk user sendiri
            // user notification removed per request (admin-only notification retained)

        // Notifikasi untuk semua admin
        $admins = User::where('role', 'admin')->get();
        $monthNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $monthLabel = $monthNames[$payment->month] ?? ($payment->month ?? '');
        foreach ($admins as $admin) {
            Notification::create([
                'type' => 'payment_upload_user',
                'title' => Auth::user()->name . ' telah mengunggah bukti bayar bulan ' . strtolower($monthLabel) . '.',
                'message' => 'Periksa bukti pembayaran dari penghuni tersebut.',
                'data' => ['user_id' => $admin->id, 'payment_id' => $payment->id, 'room_id' => $payment->room_id ?? $currentRoomId],
                'is_read' => false,
            ]);
        }
        // 🔔 Akhir tambahan

        return redirect()->route('userpage')
            ->with('success', 'Bukti pembayaran berhasil diunggah!');
    }

    public function create(Request $request)
    {
        $rooms = Room::all();
        $payment = null;
        if ($request->has('payment_id')) {
            $payment = Payment::where('id', $request->payment_id)->where('user_id', auth()->id())->first();
        }
        return view('payment.create', compact('rooms', 'payment'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'amount' => 'required|numeric|min:0',
            'paid_at' => 'nullable|date',
            'proof_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // If a specific payment_id is provided, treat this as re-upload for that payment
        if ($request->filled('payment_id')) {
            $payment = Payment::where('id', $request->payment_id)->where('user_id', $request->user_id)->firstOrFail();
            // Only allow re-upload if previously rejected
            if ($payment->status !== 'rejected') {
                return redirect()->route('payment.create')->with('error', 'Pembayaran tidak dalam status ditolak, tidak dapat mengunggah ulang.');
            }

            if ($request->hasFile('proof_path')) {
                if ($payment->proof_path && Storage::disk('public')->exists($payment->proof_path)) {
                    Storage::disk('public')->delete($payment->proof_path);
                }
                $file = $request->file('proof_path');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads/payments', $filename, 'public');
                $payment->update([
                    'proof_path' => $path,
                    'paid_at' => $request->paid_at ?? now(),
                    'amount' => $request->amount ?? $payment->amount,
                    'status' => 'pending',
                    'room_id' => $request->room_id ?? $payment->room_id,
                ]);

                // notify admins
                $user = User::find($request->user_id);
                $admins = User::where('role', 'admin')->get();
                    $monthNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                    $monthLabel = $monthNames[$payment->month] ?? ($payment->month ?? '');
                    foreach ($admins as $admin) {
                        Notification::create([
                            'type' => 'payment_upload_user',
                            'title' => $user->name . ' telah mengunggah bukti bayar bulan ' . strtolower($monthLabel) . '.',
                            'message' => 'Periksa bukti pembayaran dari penghuni tersebut.',
                            'data' => ['user_id' => $admin->id, 'payment_id' => $payment->id, 'room_id' => $payment->room_id ?? $user->room->id ?? null],
                            'is_read' => false,
                        ]);
                    }

                // Log activity for re-upload so it appears in Last Transactions
                \App\Models\Activity::create([
                    'user_id' => $user->id,
                    'action' => 'upload_payment_proof',
                    'meta' => [
                        'payment_id' => $payment->id,
                        'month' => $payment->month,
                        'year' => $payment->year,
                        'room_number' => optional($user->room)->number,
                        'tenant_name' => $user->name,
                    ],
                ]);

                return redirect()->route('userpage')->with('success', 'Bukti pembayaran berhasil diunggah ulang!');
            }

            return redirect()->route('payment.create')->with('error', 'Silakan upload bukti pembayaran untuk mengirim ulang.');
        }

        $existing = Payment::where('user_id', $request->user_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if ($existing) {
            // If previous payment was rejected, allow updating (re-upload)
            if ($existing->status === 'rejected') {
                // If file provided, store it and update the payment
                if ($request->hasFile('proof_path')) {
                    // delete old file if any
                    if ($existing->proof_path && Storage::disk('public')->exists($existing->proof_path)) {
                        Storage::disk('public')->delete($existing->proof_path);
                    }
                    $file = $request->file('proof_path');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('uploads/payments', $filename, 'public');
                    $existing->update([
                        'proof_path' => $path,
                        'paid_at' => now(),
                        'amount' => $request->amount ?? $existing->amount,
                        'status' => 'pending',
                        'room_id' => $request->room_id ?? $existing->room_id,
                    ]);

                    // notify admins about new upload (same as create flow)
                    $user = User::find($request->user_id);
                    $admins = User::where('role', 'admin')->get();
                        $monthNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                        $monthLabel = $existing->month;
                        foreach ($admins as $admin) {
                            Notification::create([
                                'type' => 'payment_upload_user',
                                'title' => $user->name . ' telah mengunggah bukti bayar bulan ' . strtolower($monthLabel) . '.',
                                'message' => 'Periksa bukti pembayaran dari penghuni tersebut.',
                                'data' => ['user_id' => $admin->id, 'payment_id' => $existing->id, 'room_id' => $existing->room_id ?? $user->room->id ?? null],
                                'is_read' => false,
                            ]);
                        }

                    // Log activity for re-upload from existing record
                    \App\Models\Activity::create([
                        'user_id' => $user->id,
                        'action' => 'upload_payment_proof',
                        'meta' => [
                            'payment_id' => $existing->id,
                            'month' => $existing->month,
                            'year' => $existing->year,
                            'room_number' => optional($user->room)->number,
                            'tenant_name' => $user->name,
                        ],
                    ]);

                    return redirect()->route('userpage')
                        ->with('success', 'Bukti pembayaran berhasil diunggah ulang!');
                }

                return redirect()->route('payment.create')
                    ->with('error', 'Silakan upload bukti pembayaran untuk mengirim ulang.');
            }

            // If existing payment is not rejected (e.g., pending/approved), block new creation
            return redirect()->route('payment.create')
                ->with('error', 'Anda sudah melakukan pembayaran untuk bulan dan tahun ini.');
        }

        if ($request->hasFile('proof_path')) {
            $file = $request->file('proof_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/payments', $filename, 'public');
            $validated['proof_path'] = $path;
            $validated['paid_at'] = now();
            $validated['status'] = 'pending';
        } else {
            $validated['status'] = 'unpaid';
        }

        $payment = Payment::create($validated);

        // 🔔 Notifikasi user
            // user notification removed per request (admin-only notification retained)

        // 🔔 Tambahan notifikasi admin
        $user = User::find($request->user_id);
        $admins = User::where('role', 'admin')->get();
        $monthNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $monthLabel = $monthNames[$payment->month] ?? ($validated['month'] ?? null);
        foreach ($admins as $admin) {
            Notification::create([
                'type' => 'payment_upload_user',
                'title' => $user->name . ' telah mengunggah bukti bayar bulan ' . strtolower($monthLabel) . '.',
                'message' => 'Periksa bukti pembayaran dari penghuni tersebut.',
                'data' => ['user_id' => $admin->id, 'payment_id' => $payment->id ?? null, 'room_id' => $payment->room_id ?? $user->room->id ?? null],
                'is_read' => false,
            ]);
        }

        // Log activity for create so it appears in Last Transactions
        \App\Models\Activity::create([
            'user_id' => $user->id,
            'action' => 'upload_payment_proof',
            'meta' => [
                'payment_id' => $payment->id ?? null,
                'month' => $payment->month ?? $validated['month'] ?? null,
                'year' => $payment->year ?? $validated['year'] ?? null,
                'room_number' => optional($user->room)->number,
                'tenant_name' => $user->name,
            ],
        ]);

        return redirect()->route('payment.index')
            ->with('success', 'Pembayaran berhasil disimpan!');
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'amount' => 'sometimes|integer|min:0',
            'paid_at' => 'nullable|date',
            'status' => 'nullable|in:unpaid,pending,approved,rejected',
        ]);

        $payment->update($validated);

        return back()->with('success', 'Pembayaran diperbarui');
    }

    public function destroy(Payment $payment)
    {
        if ($payment->proof_path && Storage::disk('public')->exists($payment->proof_path)) {
            Storage::disk('public')->delete($payment->proof_path);
        }

        $payment->delete();

        return back()->with('success', 'Pembayaran dihapus');
    }

    public function approve(Payment $payment)
    {
        try {
            $payment->update([
                'paid_at' => $payment->paid_at ?? now(),
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'status' => 'approved',
            ]);

            $roomNum = optional($payment->room)->number ?? '-';
            Notification::create([
                'type' => 'payment_approved',
                'title' => 'Pembayaran Disetujui',
                'message' => "Pembayaran untuk kamar {$roomNum} bulan {$payment->month}/{$payment->year} telah disetujui.",
                'data' => [
                    'payment_id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'room_id' => $payment->room_id,
                ],
            ]);

            \App\Models\Activity::create([
                'user_id' => auth()->id(),
                'action' => 'approve_payment',
                'meta' => [
                    'payment_id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'month' => $payment->month,
                    'year' => $payment->year,
                ],
            ]);

            return response()->json(['success' => true, 'message' => 'Pembayaran disetujui']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reject(Payment $payment)
    {
        try {
            if ($payment->proof_path && Storage::disk('public')->exists($payment->proof_path)) {
                Storage::disk('public')->delete($payment->proof_path);
            }

            $payment->update([
                'proof_path' => null,
                'status' => 'rejected',
                'approved_at' => null,
                'approved_by' => null,
            ]);

            $roomNum = optional($payment->room)->number ?? '-';
            Notification::create([
                'type' => 'payment_rejected',
                'title' => 'Pembayaran Ditolak',
                'message' => "Pembayaran untuk kamar {$roomNum} bulan {$payment->month}/{$payment->year} telah ditolak. Silakan upload bukti pembayaran yang valid.",
                'data' => [
                    'payment_id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'room_id' => $payment->room_id,
                ],
            ]);

            \App\Models\Activity::create([
                'user_id' => auth()->id(),
                'action' => 'reject_payment',
                'meta' => [
                    'payment_id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'month' => $payment->month,
                    'year' => $payment->year,
                ],
            ]);

            return response()->json(['success' => true, 'message' => 'Pembayaran ditolak']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
