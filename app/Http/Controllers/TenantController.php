<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Room;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TenantController extends Controller
{
    /**
     * Menampilkan daftar penghuni (tenants)
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'user')->with('room');

        // Default: tampilkan hanya yang aktif jika tidak ada filter status
        if (!$request->has('status')) {
            $query->where('status', 'active');
        } elseif ($request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $tenants = $query->orderBy('name')->get();

        return view('tenants.index', compact('tenants'));
    }

    /**
     * Menyimpan data penghuni baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'phone'       => 'nullable|string|max:50',
            'occupation'  => 'nullable|string|max:100',
            'address'     => 'nullable|string',
            'ktp'         => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Upload file KTP
        $ktpPath = $request->file('ktp')->store('ktp', 'public');

        // Buat password sementara
        $tempPassword = 'temp_' . uniqid();

        // Persiapkan data user baru
        $validated['role'] = 'user';
        $validated['status'] = 'active';
        $validated['password'] = Hash::make($tempPassword);
        $validated['ktp_path'] = $ktpPath;

        unset($validated['ktp']); // hapus dari data agar tidak ikut insert

        // Simpan ke database
        $user = User::create($validated);

        // Catat aktivitas pembuatan tenant
        \App\Models\Activity::create([
            'user_id' => auth()->id(),
            'action' => 'create_tenant',
            'meta' => [
                'tenant_id' => $user->id,
                'tenant_name' => $user->name,
            ],
        ]);

        return back()->with('success', 'Penghuni berhasil ditambahkan. User harus menggunakan "Forgot Password" untuk membuat password baru.');
    }

    /**
     * Memperbarui data penghuni
     */
    public function update(Request $request, User $tenant)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $tenant->id,
            'phone'       => 'nullable|string|max:50',
            'occupation'  => 'nullable|string|max:100',
            'address'     => 'nullable|string',
            'ktp'         => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'password'    => 'nullable|string|min:6',
            'room_id'     => 'nullable|exists:rooms,id',
        ]);

        // Jika ada file KTP baru
        if ($request->hasFile('ktp')) {
            // Hapus KTP lama jika ada
            if ($tenant->ktp_path && Storage::disk('public')->exists($tenant->ktp_path)) {
                Storage::disk('public')->delete($tenant->ktp_path);
            }

            $validated['ktp_path'] = $request->file('ktp')->store('ktp', 'public');
        }

        // Jika password baru diisi
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        unset($validated['ktp']); // jangan simpan file object
        $tenant->update($validated);

        /**
         * Penanganan perpindahan atau penetapan kamar
         */
        if ($request->filled('room_id')) {
            $targetRoom = Room::find($request->room_id);

            if ($targetRoom) {
                // Jika penghuni pindah kamar
                if ($tenant->room && $tenant->room->id !== $targetRoom->id) {
                    $tenant->room->update(['user_id' => null]);
                }

                // Pastikan kamar target kosong atau milik tenant ini
                if (!$targetRoom->user_id || $targetRoom->user_id === $tenant->id) {
                    // Hapus relasi kamar lain milik tenant ini
                    Room::where('user_id', $tenant->id)
                        ->where('id', '!=', $targetRoom->id)
                        ->update(['user_id' => null]);

                    // Set kamar baru
                    $targetRoom->update(['user_id' => $tenant->id]);
                }
            }
        } else {
            // Jika room_id dikosongkan, hapus relasi kamar
            if ($tenant->room) {
                $tenant->room->update(['user_id' => null]);
            }
        }

        // Catat aktivitas update tenant
        \App\Models\Activity::create([
            'user_id' => auth()->id(),
            'action' => 'update_tenant',
            'meta' => [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
            ],
        ]);

        return back()->with('success', 'Data penghuni berhasil diperbarui.');
    }

    /**
     * Menghapus penghuni
     */
    public function destroy(User $tenant)
    {
        // Lepas kamar jika masih menempati
        if ($tenant->room) {
            $tenant->room->update(['user_id' => null]);
        }

        // Hapus file KTP jika ada
        if ($tenant->ktp_path && Storage::disk('public')->exists($tenant->ktp_path)) {
            Storage::disk('public')->delete($tenant->ktp_path);
        }

        $tenant->delete();

        return back()->with('success', 'Penghuni berhasil dihapus.');
    }

    /**
     * Mengambil daftar pembayaran tenant
     */
    public function payments(User $tenant)
    {
        $payments = $tenant->payments()
            ->where('status', 'approved')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        return response()->json($payments);
    }

    /**
     * Mengaktifkan kembali tenant
     */
    public function activate(User $tenant)
    {
        $tenant->update(['status' => 'active']);

        return back()->with('success', 'Penghuni berhasil diaktifkan.');
    }

    /**
     * Menonaktifkan tenant
     */
    public function deactivate(User $tenant)
    {
        // Lepaskan kamar tanpa mengubah status kamar
        if ($tenant->room) {
            $tenant->room->update(['user_id' => null]);
        }

        $tenant->update(['status' => 'deactive']);

        return back()->with('success', 'Penghuni berhasil dinonaktifkan.');
    }
}
