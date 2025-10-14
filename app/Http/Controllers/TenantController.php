<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'user')->with('room');

        // Default filter: show only active if no status param
        if (!$request->has('status')) {
            $query->where('status', 'active');
        } elseif ($request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        $tenants = $query->orderBy('name')->get();
        return view('tenants.index', compact('tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'ktp' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'password' => 'required|string|min:6',
        ]);
        
        // Handle KTP file upload
        $ktpPath = null;
        if ($request->hasFile('ktp')) {
            $ktpPath = $request->file('ktp')->store('ktp', 'public');
        }
        
        $validated['role'] = 'user';
        $validated['status'] = 'active';
        $validated['password'] = Hash::make($validated['password']);
        $validated['ktp_path'] = $ktpPath;
        unset($validated['ktp']); // Remove the file from validated data
        
        $user = User::create($validated);
        return back()->with('success', 'Penghuni ditambahkan');
    }

    public function update(Request $request, User $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $tenant->id,
            'phone' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'ktp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'password' => 'nullable|string|min:6',
            'room_id' => 'nullable|exists:rooms,id',
        ]);
        
        // Handle KTP file upload
        if ($request->hasFile('ktp')) {
            // Delete old KTP file if exists
            if ($tenant->ktp_path) {
                Storage::disk('public')->delete($tenant->ktp_path);
            }
            $validated['ktp_path'] = $request->file('ktp')->store('ktp', 'public');
        }
        
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        
        unset($validated['ktp']); // Remove the file from validated data
        $tenant->update($validated);

        // Handle room assignment/move
        if ($request->filled('room_id')) {
            $targetRoom = Room::find($request->input('room_id'));
            if ($targetRoom) {
                // Free previous room if different
                if ($tenant->room && $tenant->room->id !== $targetRoom->id) {
                    $tenant->room->update(['user_id' => null]);
                }
                // Assign if room is empty or already assigned to this tenant
                if (!$targetRoom->user_id || $targetRoom->user_id === $tenant->id) {
                    // Ensure tenant is not occupying another room
                    Room::where('user_id', $tenant->id)
                        ->where('id', '!=', $targetRoom->id)
                        ->update(['user_id' => null]);
                    $targetRoom->update(['user_id' => $tenant->id]);
                }
            }
        } else {
            // If room_id empty, detach tenant from current room
            if ($tenant->room) {
                $tenant->room->update(['user_id' => null]);
            }
        }
        return back()->with('success', 'Penghuni diperbarui');
    }

    public function destroy(User $tenant)
    {
        $tenant->delete();
        return back()->with('success', 'Penghuni dihapus');
    }

    public function payments(User $tenant)
    {
        $payments = $tenant->payments()->orderByDesc('year')->orderByDesc('month')->get();
        return response()->json($payments);
    }

    public function activate(User $tenant)
    {
        $tenant->update(['status' => 'active']);
        return back()->with('success', 'Penghuni diaktifkan');
    }

    public function deactivate(User $tenant)
    {
        // Remove tenant from room when deactivated (no room status updates)
        if ($tenant->room) {
            $tenant->room->update(['user_id' => null]);
        }
        
        $tenant->update(['status' => 'deactive']);
        return back()->with('success', 'Penghuni dinonaktifkan');
    }

    // Room status activation/deactivation removed as room status is not used
}


