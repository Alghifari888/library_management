<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // Model User
use App\Models\Borrowing; // Untuk cek peminjaman aktif
use App\Http\Requests\Admin\MemberStoreRequest;
use App\Http\Requests\Admin\MemberUpdateRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua user dengan role 'anggota'
        $members = User::where('role', User::ROLE_ANGGOTA)
                       ->latest()
                       ->paginate(10);
        return view('admin.members.index', compact('members'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.members.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MemberStoreRequest $request)
    {
        $validatedData = $request->validated();

        // Hash password
        $validatedData['password'] = Hash::make($validatedData['password']);
        // Set role default
        $validatedData['role'] = User::ROLE_ANGGOTA;
        // Tandai email sebagai terverifikasi karena dibuat oleh admin
        $validatedData['email_verified_at'] = now();

        // Handle upload foto profil
        if ($request->hasFile('profile_photo')) {
            // Simpan file ke storage/app/public/profile-photos
            $filePath = $request->file('profile_photo')->store('profile-photos', 'public');
            $validatedData['profile_photo_path'] = $filePath;
        }

        User::create($validatedData);

        return redirect()->route('admin.members.index')
                         ->with('success', 'Anggota baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     * (Untuk anggota, biasanya langsung ke edit)
     */
    public function show(User $member) // Route model binding akan mengambil user berdasarkan ID
    {
        // Pastikan user yang diakses adalah anggota, jika tidak, redirect atau tampilkan error
        if (!$member->hasRole(User::ROLE_ANGGOTA)) {
            return redirect()->route('admin.members.index')
                             ->with('error', 'Pengguna yang dipilih bukan anggota.');
        }
        return redirect()->route('admin.members.edit', $member);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $member)
    {
        if (!$member->hasRole(User::ROLE_ANGGOTA)) {
             return redirect()->route('admin.members.index')
                             ->with('error', 'Pengguna yang dipilih bukan anggota.');
        }
        return view('admin.members.edit', compact('member'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MemberUpdateRequest $request, User $member)
    {
        if (!$member->hasRole(User::ROLE_ANGGOTA)) {
             return redirect()->route('admin.members.index')
                             ->with('error', 'Gagal memperbarui. Pengguna yang dipilih bukan anggota.');
        }

        $validatedData = $request->validated();

        // Update password jika diisi
        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']); // Jangan update password jika kosong
        }

        // Handle upload foto profil baru
        if ($request->hasFile('profile_photo')) {
            // Hapus foto lama jika ada
            if ($member->profile_photo_path && Storage::disk('public')->exists($member->profile_photo_path)) {
                Storage::disk('public')->delete($member->profile_photo_path);
            }
            // Simpan foto baru
            $filePath = $request->file('profile_photo')->store('profile-photos', 'public');
            $validatedData['profile_photo_path'] = $filePath;
        }

        $member->update($validatedData);

        return redirect()->route('admin.members.index')
                         ->with('success', 'Data anggota berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $member)
    {
        if (!$member->hasRole(User::ROLE_ANGGOTA)) {
             return redirect()->route('admin.members.index')
                             ->with('error', 'Gagal menghapus. Pengguna yang dipilih bukan anggota.');
        }

        // Periksa apakah anggota memiliki peminjaman yang aktif/belum selesai
        if ($member->borrowings()->whereIn('status', [Borrowing::STATUS_BORROWED, Borrowing::STATUS_OVERDUE])->exists()) {
            return redirect()->route('admin.members.index')
                             ->with('error', 'Anggota tidak dapat dihapus karena memiliki peminjaman yang belum selesai.');
        }
        
        // Hapus foto profil dari storage jika ada
        if ($member->profile_photo_path && Storage::disk('public')->exists($member->profile_photo_path)) {
            Storage::disk('public')->delete($member->profile_photo_path);
        }

        $member->delete();

        return redirect()->route('admin.members.index')
                         ->with('success', 'Data anggota berhasil dihapus.');
    }
}