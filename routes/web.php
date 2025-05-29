<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController; // Alias untuk CategoryController Admin

// Halaman Welcome
Route::get('/', function () {
    return view('welcome');
});

// Dashboard default (setelah login)
// Akan kita arahkan ke dashboard spesifik role nanti
Route::get('/dashboard', function () {
    // Logika untuk mengarahkan berdasarkan role bisa ditambahkan di sini atau di controller terpisah
    $user = Auth::user();
    if ($user->hasRole(\App\Models\User::ROLE_ADMIN)) {
        // return redirect()->route('admin.dashboard'); // Buat route ini nanti
        return view('dashboard', ['userRole' => 'Admin']); 
    } elseif ($user->hasRole(\App\Models\User::ROLE_PETUGAS)) {
        // return redirect()->route('officer.dashboard'); // Buat route ini nanti
         return view('dashboard', ['userRole' => 'Petugas']);
    } elseif ($user->hasRole(\App\Models\User::ROLE_ANGGOTA)) {
        // return redirect()->route('member.dashboard'); // Buat route ini nanti
         return view('dashboard', ['userRole' => 'Anggota']);
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// Rute Profile dari Breeze
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Grup Rute untuk Admin
Route::middleware(['auth', 'role:' . \App\Models\User::ROLE_ADMIN])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard'); // Buat nanti

        // CRUD Kategori
        Route::resource('categories', AdminCategoryController::class);
        // Route::get('categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        // Route::get('categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
        // Route::post('categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        // Route::get('categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        // Route::put('categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        // Route::delete('categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
        // Route::get('categories/{category}', [AdminCategoryController::class, 'show'])->name('categories.show'); // Jika perlu show view

        // Tambahkan resource lain untuk Admin di sini (Books, Members, Borrowings, Fines)
});


// Grup Rute untuk Petugas (Contoh, bisa dibuat nanti)
// Route::middleware(['auth', 'role:' . \App\Models\User::ROLE_PETUGAS])
//     ->prefix('officer')
//     ->name('officer.')
//     ->group(function () {
//         // Route::get('/dashboard', [OfficerDashboardController::class, 'index'])->name('dashboard');
//         // ... route petugas lainnya
//     });

// Grup Rute untuk Anggota (Contoh, bisa dibuat nanti)
// Route::middleware(['auth', 'role:' . \App\Models\User::ROLE_ANGGOTA])
//     ->prefix('member')
//     ->name('member.')
//     ->group(function () {
//         // Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');
//         // ... route anggota lainnya
//     });


require __DIR__.'/auth.php'; // Route otentikasi dari Breeze