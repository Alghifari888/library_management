<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\User;
use App\Models\Book;
use App\Models\Fine; // BARU: Import model Fine
use App\Http\Requests\Admin\BorrowingStoreRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // BARU: Import DB Facade untuk transaksi

class BorrowingController extends Controller
{
    private const MAX_BORROWED_BOOKS = 3;
    private const DEFAULT_BORROWING_DURATION = 7;

    public function index(Request $request)
    {
        $query = Borrowing::with(['user', 'book'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

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

        $borrowings = $query->paginate(10)->withQueryString();

        return view('admin.borrowings.index', compact('borrowings'));
    }

    public function create()
    {
        $members = User::where('role', User::ROLE_ANGGOTA)->orderBy('name')->get();
        $books = Book::where('available_quantity', '>', 0)->orderBy('title')->get();
        
        $defaultBorrowedAt = Carbon::today()->toDateString();
        $defaultDueAt = Carbon::today()->addDays(self::DEFAULT_BORROWING_DURATION)->toDateString();

        return view('admin.borrowings.create', compact('members', 'books', 'defaultBorrowedAt', 'defaultDueAt'));
    }

    public function store(BorrowingStoreRequest $request)
    {
        $validatedData = $request->validated();
        
        // Gunakan DB Transaction untuk memastikan konsistensi data
        DB::beginTransaction();
        try {
            $book = Book::lockForUpdate()->find($validatedData['book_id']); // Lock baris buku untuk update
            $member = User::find($validatedData['user_id']);

            if (!$member || !$member->hasRole(User::ROLE_ANGGOTA)) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Pengguna yang dipilih bukan anggota atau tidak valid.')->withInput();
            }
            if (!$book || $book->available_quantity <= 0) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Buku tidak ditemukan atau stok sedang habis.')->withInput();
            }
            $currentMemberBorrows = $member->borrowings()
                                          ->whereIn('status', [Borrowing::STATUS_BORROWED, Borrowing::STATUS_OVERDUE])
                                          ->count();
            if ($currentMemberBorrows >= self::MAX_BORROWED_BOOKS) {
                DB::rollBack();
                return redirect()->back()->with('error', "Anggota telah mencapai batas maksimal peminjaman (" . self::MAX_BORROWED_BOOKS . " buku).")->withInput();
            }

            $borrowing = new Borrowing();
            $borrowing->user_id = $validatedData['user_id'];
            $borrowing->book_id = $validatedData['book_id'];
            $borrowing->borrowed_at = Carbon::parse($validatedData['borrowed_at']);
            $borrowing->due_at = Carbon::parse($validatedData['due_at']);
            $borrowing->status = Borrowing::STATUS_BORROWED;
            $borrowing->processed_by_user_id = Auth::id();
            $borrowing->save();

            $book->decrement('available_quantity');
            
            DB::commit();
            return redirect()->route('admin.borrowings.index')
                             ->with('success', 'Peminjaman buku berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log error $e->getMessage()
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mencatat peminjaman. Silakan coba lagi.')->withInput();
        }
    }

    public function returnBook(Request $request, Borrowing $borrowing)
    {
        if (!in_array($borrowing->status, [Borrowing::STATUS_BORROWED, Borrowing::STATUS_OVERDUE]) || $borrowing->returned_at) {
            return redirect()->route('admin.borrowings.index')
                             ->with('error', 'Buku ini tidak dalam status dipinjam atau sudah dikembalikan.');
        }

        // Gunakan DB Transaction
        DB::beginTransaction();
        try {
            $book = Book::lockForUpdate()->find($borrowing->book_id); // Lock baris buku
            $returnedDate = Carbon::now(); // Tanggal kembali adalah saat ini

            $borrowing->returned_at = $returnedDate;
            
            $daysLate = 0;
            if ($returnedDate->isAfter($borrowing->due_at)) {
                // Hitung selisih hari hanya jika tanggal kembali > tanggal jatuh tempo
                // diffInDays akan menghasilkan nilai absolut, kita inginkan positif jika terlambat
                $daysLate = $borrowing->due_at->diffInDays($returnedDate);
            }

            if ($daysLate > 0) {
                $borrowing->status = Borrowing::STATUS_OVERDUE; // Tandai sebagai overdue jika terlambat
                                                               // Meskipun akan diupdate lagi ke RETURNED, ini bisa jadi catatan
                                                               // jika logika status lebih kompleks
                // Buat record denda
                Fine::create([
                    'borrowing_id' => $borrowing->id,
                    'user_id' => $borrowing->user_id,
                    'amount' => $daysLate * Fine::RATE_PER_DAY,
                    'reason' => "Keterlambatan pengembalian buku selama {$daysLate} hari.",
                    'status' => Fine::STATUS_UNPAID, // Default status denda adalah belum dibayar
                ]);
            }
            
            $borrowing->status = Borrowing::STATUS_RETURNED; // Status akhir selalu dikembalikan
            $borrowing->save();

            // Tambah kembali stok buku yang tersedia
            if ($book) { // Pastikan buku ditemukan
                 $book->increment('available_quantity');
            }
            
            DB::commit();

            $successMessage = 'Buku berhasil ditandai sebagai telah dikembalikan.';
            if ($daysLate > 0) {
                $successMessage .= " Denda keterlambatan sebesar Rp " . number_format($daysLate * Fine::RATE_PER_DAY, 0, ',', '.') . " telah dicatat.";
            }

            return redirect()->route('admin.borrowings.index')
                             ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log error $e->getMessage()
            return redirect()->route('admin.borrowings.index')
                             ->with('error', 'Terjadi kesalahan saat memproses pengembalian buku. Silakan coba lagi.');
        }
    }
}