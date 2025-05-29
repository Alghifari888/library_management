<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\User;
use App\Models\Book;
use App\Http\Requests\Admin\BorrowingStoreRequest;
use Illuminate\Http\Request;
use Carbon\Carbon; // Untuk manipulasi tanggal
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan ID user yang login

class BorrowingController extends Controller
{
    // Batas maksimal buku yang boleh dipinjam anggota
    private const MAX_BORROWED_BOOKS = 3; // Anda bisa pindahkan ini ke file config jika mau
    // Durasi peminjaman default (dalam hari)
    private const DEFAULT_BORROWING_DURATION = 7; // Anda bisa pindahkan ini ke file config


    /**
     * Display a listing of the borrowings.
     */
    public function index(Request $request)
    {
        $query = Borrowing::with(['user', 'book'])->latest();

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan pencarian (nama anggota atau judul buku)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('user', function($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'like', "%{$searchTerm}%");
                })->orWhereHas('book', function($bookQuery) use ($searchTerm) {
                    $bookQuery->where('title', 'like', "%{$searchTerm}%");
                });
            });
        }

        $borrowings = $query->paginate(10)->withQueryString(); // withQueryString agar filter tetap saat paginasi

        return view('admin.borrowings.index', compact('borrowings'));
    }

    /**
     * Show the form for creating a new borrowing.
     */
    public function create()
    {
        // Ambil hanya user dengan role 'anggota'
        $members = User::where('role', User::ROLE_ANGGOTA)->orderBy('name')->get();
        // Ambil hanya buku yang memiliki stok tersedia > 0
        $books = Book::where('available_quantity', '>', 0)->orderBy('title')->get();
        
        $defaultBorrowedAt = Carbon::today()->toDateString();
        $defaultDueAt = Carbon::today()->addDays(self::DEFAULT_BORROWING_DURATION)->toDateString();

        return view('admin.borrowings.create', compact('members', 'books', 'defaultBorrowedAt', 'defaultDueAt'));
    }

    /**
     * Store a newly created borrowing in storage.
     */
    public function store(BorrowingStoreRequest $request)
    {
        $validatedData = $request->validated();

        $book = Book::find($validatedData['book_id']);
        $member = User::find($validatedData['user_id']);

        // 1. Cek apakah user adalah anggota
        if (!$member || !$member->hasRole(User::ROLE_ANGGOTA)) {
            return redirect()->back()->with('error', 'Pengguna yang dipilih bukan anggota atau tidak valid.')->withInput();
        }

        // 2. Cek ketersediaan buku
        if (!$book || $book->available_quantity <= 0) { // Tambahkan pengecekan $book ada
            return redirect()->back()->with('error', 'Buku tidak ditemukan atau stok sedang habis.')->withInput();
        }

        // 3. Cek batas maksimal peminjaman anggota
        $currentMemberBorrows = $member->borrowings()
                                      ->whereIn('status', [Borrowing::STATUS_BORROWED, Borrowing::STATUS_OVERDUE])
                                      ->count();
        
        if ($currentMemberBorrows >= self::MAX_BORROWED_BOOKS) {
            return redirect()->back()->with('error', "Anggota telah mencapai batas maksimal peminjaman (" . self::MAX_BORROWED_BOOKS . " buku).")->withInput();
        }

        // Lanjutkan proses peminjaman
        $borrowing = new Borrowing();
        $borrowing->user_id = $validatedData['user_id'];
        $borrowing->book_id = $validatedData['book_id'];
        $borrowing->borrowed_at = Carbon::parse($validatedData['borrowed_at']);
        $borrowing->due_at = Carbon::parse($validatedData['due_at']);
        $borrowing->status = Borrowing::STATUS_BORROWED; // Gunakan konstanta dari model
        $borrowing->processed_by_user_id = Auth::id(); // User yang memproses (Admin/Petugas)
        $borrowing->save();

        // Kurangi stok buku yang tersedia
        $book->decrement('available_quantity');

        return redirect()->route('admin.borrowings.index')
                         ->with('success', 'Peminjaman buku berhasil dicatat.');
    }
    
    // Metode untuk returnBook dan lainnya akan ditambahkan di Part 2 modul ini
    public function returnBook(Request $request, Borrowing $borrowing)
    {
        // Pastikan buku memang sedang dipinjam atau terlambat sebelum diproses pengembaliannya
        if (!in_array($borrowing->status, [Borrowing::STATUS_BORROWED, Borrowing::STATUS_OVERDUE]) || $borrowing->returned_at) {
            return redirect()->route('admin.borrowings.index')
                             ->with('error', 'Buku ini tidak dalam status dipinjam atau sudah dikembalikan.');
        }

        // Tentukan tanggal kembali. Bisa dari input form jika ada, atau tanggal saat ini.
        // Untuk sekarang, kita gunakan tanggal saat ini.
        $returnedDate = Carbon::now();
        // Anda bisa menambahkan input tanggal pengembalian aktual jika diperlukan:
        // $request->validate(['returned_at_actual' => 'required|date|after_or_equal:'.$borrowing->borrowed_at->toDateString()]);
        // $returnedDate = Carbon::parse($request->returned_at_actual);


        $borrowing->returned_at = $returnedDate;
        
        // Tentukan status akhir: DIKEMBALIKAN atau DIKEMBALIKAN_TERLAMBAT (jika modul denda ada)
        // Untuk sekarang, jika returned_at > due_at, maka status jadi OVERDUE (jika belum), lalu RETURNED.
        // Atau cukup set RETURNED, dan logika denda akan cek ini.
        // Jika returned_at diisi, dan > due_at, maka secara implisit terlambat.
        
        // Jika tanggal kembali melewati jatuh tempo, dan statusnya masih 'borrowed', ubah jadi 'overdue' dulu
        // Ini lebih untuk pencatatan internal jika diperlukan, karena denda akan dihitung berdasarkan perbandingan tanggal.
        if ($returnedDate->isAfter($borrowing->due_at) && $borrowing->status == Borrowing::STATUS_BORROWED) {
            $borrowing->status = Borrowing::STATUS_OVERDUE;
        }
        // Kemudian, update status akhir menjadi RETURNED
        // Atau bisa juga: jika terlambat, status = 'returned_late', jika tepat waktu 'returned_on_time'
        // Untuk kesederhanaan sesuai ERD awal:
        $borrowing->status = Borrowing::STATUS_RETURNED;
        
        $borrowing->save();

        // Tambah kembali stok buku yang tersedia
        $borrowing->book()->increment('available_quantity');

        // Di sini nanti bisa ditambahkan logika untuk pembuatan denda jika terlambat
        // if ($returnedDate->isAfter($borrowing->due_at)) {
        //     // Buat record denda
        //     // Fine::create([...]);
        // }

        return redirect()->route('admin.borrowings.index')
                         ->with('success', 'Buku berhasil ditandai sebagai telah dikembalikan.');
    }
}
