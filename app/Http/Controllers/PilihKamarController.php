<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;

class PilihKamarController extends Controller
{
    public function index()
    {
        // Ambil kamar yang belum disewa (user_id masih null)
        $rooms = Room::whereNull('user_id')->get();

        return view('choose-room.index', compact('rooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:rooms,id',
        ]);

        // Ambil kamar yang dipilih
        $room = Room::findOrFail($request->id);

        // Pastikan kamar belum disewa
        if ($room->user_id !== null) {
            return back()->with('error', 'Kamar ini sudah ditempati.');
        }

        // Update kamar: isi user_id
        $room->user_id = Auth::id();
        $room->save();

        // Redirect ke halaman dashboard user
        return redirect()->route('userpage')->with('success', 'Kamar berhasil dipilih!');
    }
}
