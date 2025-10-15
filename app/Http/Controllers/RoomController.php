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

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:50|unique:rooms,number,' . $room->id,
            'user_id' => 'nullable|exists:users,id',
            'tenant_status' => 'nullable|in:active,deactive',
        ]);
        // If assigning a user to this room, make sure that user is not assigned elsewhere
        if (!empty($validated['user_id'])) {
            // Detach user from any other room
            Room::where('user_id', $validated['user_id'])
                ->where('id', '!=', $room->id)
                ->update(['user_id' => null]);
        }

        $room->update(collect($validated)->only(['number','user_id'])->toArray());

        // Optionally update tenant status if provided
        if (!empty($validated['tenant_status']) && !empty($validated['user_id'])) {
            $tenant = User::find($validated['user_id']);
            if ($tenant) {
                $tenant->update(['status' => $validated['tenant_status']]);
            }
        }
        
        // Create activity for room update
        \App\Models\Activity::create([
            'user_id' => auth()->id(),
            'action' => 'update_room',
            'meta' => [
                'room_id' => $room->id,
                'room_number' => $room->number,
                'tenant_name' => optional($room->tenant)->name,
            ],
        ]);
        
        return back()->with('success', 'Kamar diperbarui');
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


