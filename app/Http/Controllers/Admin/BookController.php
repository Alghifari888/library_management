<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Borrowing; // <--- TAMBAHKAN BARIS INI
use App\Http\Requests\Admin\BookStoreRequest;
use App\Http\Requests\Admin\BookUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage; // Untuk manajemen file

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::with('category')->latest()->paginate(10);
        return view('admin.books.index', compact('books'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.books.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BookStoreRequest $request)
    {
        $validatedData = $request->validated();

        // Handle file upload untuk sampul buku
        if ($request->hasFile('cover_image')) {
            // Simpan file ke storage/app/public/covers
            // Nama file akan di-generate unik untuk menghindari konflik
            $filePath = $request->file('cover_image')->store('covers', 'public');
            $validatedData['cover_image_path'] = $filePath;
        }

        // Generate slug (model juga sudah bisa handle ini)
        $validatedData['slug'] = Str::slug($validatedData['title']);
        
        // Set available_quantity sama dengan stock_quantity saat buku baru dibuat
        $validatedData['available_quantity'] = $validatedData['stock_quantity'];

        Book::create($validatedData);

        return redirect()->route('admin.books.index')
                         ->with('success', 'Buku berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        // Biasanya tidak ada halaman show khusus untuk buku di admin,
        // detail bisa dilihat di index atau edit.
        // Kita bisa redirect ke halaman edit.
        return redirect()->route('admin.books.edit', $book);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.books.edit', compact('book', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookUpdateRequest $request, Book $book)
    {
        $validatedData = $request->validated();

        // Handle file upload jika ada sampul baru
        if ($request->hasFile('cover_image')) {
            // Hapus sampul lama jika ada
            if ($book->cover_image_path && Storage::disk('public')->exists($book->cover_image_path)) {
                Storage::disk('public')->delete($book->cover_image_path);
            }
            // Simpan sampul baru
            $filePath = $request->file('cover_image')->store('covers', 'public');
            $validatedData['cover_image_path'] = $filePath;
        }

        // Update slug jika judul berubah (model juga bisa handle ini)
        if ($book->title !== $validatedData['title']) {
            $validatedData['slug'] = Str::slug($validatedData['title']);
        }
        
        // Logika untuk available_quantity saat stock_quantity diupdate:
        // Jika stock_quantity berubah, available_quantity perlu disesuaikan.
        // Perbedaan antara stock baru dan lama ditambahkan ke available.
        // Ini asumsi sederhana, bisa lebih kompleks jika ada buku yang sedang dipinjam.
        // Untuk sekarang, kita asumsikan available_quantity mengikuti perubahan stock,
        // dengan memperhitungkan jumlah yang sedang dipinjam.
        $borrowedCount = $book->borrowings()->whereIn('status', [Borrowing::STATUS_BORROWED, Borrowing::STATUS_OVERDUE])->count();
        $validatedData['available_quantity'] = $validatedData['stock_quantity'] - $borrowedCount;


        $book->update($validatedData);

        return redirect()->route('admin.books.index')
                         ->with('success', 'Buku berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        // Periksa apakah buku sedang dipinjam
        if ($book->borrowings()->whereIn('status', ['borrowed', 'overdue'])->exists()) {
            return redirect()->route('admin.books.index')
                             ->with('error', 'Buku tidak dapat dihapus karena sedang dalam proses peminjaman.');
        }

        // Hapus file sampul dari storage jika ada
        if ($book->cover_image_path && Storage::disk('public')->exists($book->cover_image_path)) {
            Storage::disk('public')->delete($book->cover_image_path);
        }

        $book->delete();

        return redirect()->route('admin.books.index')
                         ->with('success', 'Buku berhasil dihapus.');
    }
}