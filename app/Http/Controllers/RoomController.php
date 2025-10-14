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
        Room::create($validated);
        return back()->with('success', 'Kamar ditambahkan');
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:50|unique:rooms,number,' . $room->id,
            'user_id' => 'nullable|exists:users,id',
        ]);
        $room->update($validated);
        return back()->with('success', 'Kamar diperbarui');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return back()->with('success', 'Kamar dihapus');
    }

    public function payments(Room $room)
    {
        $payments = $room->payments()->with('user')->orderByDesc('year')->orderByDesc('month')->get();
        return response()->json($payments);
    }
}


