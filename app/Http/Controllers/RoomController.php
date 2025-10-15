<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('tenant')->orderBy('number')->get();
        return view('rooms.index', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:50|unique:rooms,number',
        ]);
        $room = Room::create($validated);
        
        // Create activity for room creation
        \App\Models\Activity::create([
            'user_id' => auth()->id(),
            'action' => 'create_room',
            'meta' => [
                'room_id' => $room->id,
                'room_number' => $room->number,
            ],
        ]);
        
        return back()->with('success', 'Kamar ditambahkan');
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'number' => 'required|string|max:50',
        'user_id' => 'nullable|exists:users,id',
    ]);

    $room = Room::findOrFail($id);

    // Keep old data for activity
    $oldNumber = $room->number;
    $oldUserId = $room->user_id;

    // Update room number
    $room->number = $request->number;

    // Handle tenant assignment: if user_id provided, assign to this room (and free previous room)
    $newUserId = $request->input('user_id') ?: null;

    if ($newUserId) {
        // Free any room currently occupied by this user (except this room)
        Room::where('user_id', $newUserId)->where('id', '!=', $room->id)->update(['user_id' => null]);
        $room->user_id = $newUserId;
    } else {
        // If admin sets empty, detach user from this room
        $room->user_id = null;
    }

    $room->save();

    // Update tenant status if provided
    if ($request->has('tenant_status') && $newUserId) {
        $tenant = \App\Models\User::find($newUserId);
        if ($tenant) {
            $tenant->status = $request->tenant_status;
            $tenant->save();
        }
    }

    // If room occupant changed, update previous occupant record
    if ($oldUserId && $oldUserId != $newUserId) {
        $prevTenant = \App\Models\User::find($oldUserId);
        if ($prevTenant) {
            // detach prev tenant from this room
            // (Room model already updated, just ensure consistency)
            // no extra field on user needed
        }
    }

    // Create activity for room update
    \App\Models\Activity::create([
        'user_id' => auth()->id(),
        'action' => 'update_room',
        'meta' => [
            'room_id' => $room->id,
            'room_number_old' => $oldNumber,
            'room_number_new' => $room->number,
            'old_user_id' => $oldUserId,
            'new_user_id' => $newUserId,
        ],
    ]);

    return redirect()->back()->with('success', 'Data kamar berhasil diperbarui.');
}


    public function destroy(Room $room)
    {
        $room->delete();
        return back()->with('success', 'Kamar dihapus');
    }

    public function payments(Room $room)
    {
        $payments = $room->payments()
            ->with('user')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json($payments);
    }


    // Di RoomController atau lebih baik buat PaymentController
    public function approvePayment(Payment $payment)
    {
        if ($payment->approved_at || $payment->approved_by) {
            return response()->json(['success' => false, 'message' => 'Pembayaran sudah di-ACC']);
        }

        $payment->update([
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function rejectPayment(Payment $payment)
    {
        if ($payment->approved_at || $payment->approved_by) {
            return response()->json(['success' => false, 'message' => 'Pembayaran sudah di-ACC']);
        }

        $payment->update([
            'approved_at' => null,
            'approved_by' => null,
            'proof_path' => null, // optional: hapus bukti jika di reject
        ]);

        return response()->json(['success' => true]);
    }

}


