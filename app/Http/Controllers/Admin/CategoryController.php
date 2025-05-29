<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\Admin\CategoryStoreRequest;
use App\Http\Requests\Admin\CategoryUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // Untuk slug

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua kategori dengan paginasi
        $categories = Category::latest()->paginate(10); // Tampilkan 10 per halaman, terbaru dulu
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Tampilkan form untuk membuat kategori baru
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryStoreRequest $request)
    {
        // Validasi sudah dilakukan oleh CategoryStoreRequest
        $validatedData = $request->validated();

        // Buat slug jika belum ada, meskipun model kita sudah handle ini
        // Ini hanya sebagai contoh jika ingin kontrol lebih di controller
        if (empty($validatedData['slug'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        }
        
        Category::create($validatedData);

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     * (Opsional untuk kategori, bisa jadi tidak digunakan jika info detail ada di index/edit)
     */
    public function show(Category $category)
    {
        // Tampilkan detail satu kategori
        // return view('admin.categories.show', compact('category'));
        // Untuk kategori, biasanya redirect ke edit atau cukup di index saja.
        // Jika tidak ada view show khusus, bisa redirect ke index atau edit.
        return redirect()->route('admin.categories.edit', $category);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        // Tampilkan form untuk mengedit kategori
        // $category sudah otomatis di-resolve berkat model-route binding
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        // Validasi sudah dilakukan oleh CategoryUpdateRequest
        $validatedData = $request->validated();
        
        // Buat slug jika nama berubah dan slug tidak diisi manual
        if ($request->filled('name') && $category->name !== $request->name && empty($validatedData['slug'])) {
            $validatedData['slug'] = Str::slug($request->name);
        } elseif (empty($validatedData['slug']) && isset($category->slug)) {
            // Jika slug tidak diisi di form, pertahankan slug lama jika ada
            unset($validatedData['slug']);
        }

        $category->update($validatedData);

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Hapus kategori
        // Pertimbangkan apa yang terjadi pada buku jika kategori dihapus
        // Migration kita sudah set onDelete('cascade') pada books.category_id,
        // tapi itu untuk foreign key constraint, bukan logika bisnis jika ada buku.
        // Untuk sekarang, kita asumsikan boleh dihapus.
        // Jika ada buku terkait, mungkin lebih baik mencegah penghapusan atau memberi peringatan.

        if ($category->books()->count() > 0) {
            return redirect()->route('admin.categories.index')
                             ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki buku terkait.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Kategori berhasil dihapus.');
    }
}