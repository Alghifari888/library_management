<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\User; // Untuk filter berdasarkan user nanti
use Illuminate\Http\Request;
use Carbon\Carbon;

class FineController extends Controller
{
    /**
     * Display a listing of the fines.
     */
    public function index(Request $request)
    {
        $query = Fine::with(['user', 'borrowing.book']) // Eager load relasi
                     ->latest('fines.created_at'); // Urutkan berdasarkan kapan denda dibuat

        // Filter berdasarkan status pembayaran
        if ($request->filled('status') && in_array($request->status, [Fine::STATUS_PAID, Fine::STATUS_UNPAID])) {
            $query->where('fines.status', $request->status);
        }

        // Filter berdasarkan pencarian (nama anggota, judul buku dari peminjaman, atau alasan denda)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('user', function($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'like', "%{$searchTerm}%");
                })->orWhereHas('borrowing.book', function($bookQuery) use ($searchTerm) {
                    $bookQuery->where('title', 'like', "%{$searchTerm}%");
                })->orWhere('fines.reason', 'like', "%{$searchTerm}%");
            });
        }
        
        $fines = $query->paginate(10)->withQueryString(); // withQueryString agar filter tetap saat paginasi

        return view('admin.fines.index', compact('fines'));
    }

    /**
     * Mark the specified fine as paid.
     */
    public function markAsPaid(Request $request, Fine $fine)
    {
        // Pastikan denda belum dibayar
        if ($fine->status === Fine::STATUS_PAID) {
            return redirect()->route('admin.fines.index')
                             ->with('info', 'Denda ini sudah dibayar sebelumnya.');
        }

        $fine->status = Fine::STATUS_PAID;
        $fine->paid_at = Carbon::now();
        $fine->save();

        return redirect()->route('admin.fines.index')
                         ->with('success', 'Denda berhasil ditandai sebagai sudah dibayar.');
    }
}