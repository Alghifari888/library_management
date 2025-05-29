<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BookController as AdminBookController; // BARU: Import BookController Admin
// Halaman Welcome
Route::get('/', function () {
    return view('welcome');
});

// Dashboard default (setelah login)
Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user->hasRole(\App\Models\User::ROLE_ADMIN)) {
        return view('dashboard', ['userRole' => 'Admin']); 
    } elseif ($user->hasRole(\App\Models\User::ROLE_PETUGAS)) {
         return view('dashboard', ['userRole' => 'Petugas']);
    } elseif ($user->hasRole(\App\Models\User::ROLE_ANGGOTA)) {
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
        // CRUD Kategori
        Route::resource('categories', AdminCategoryController::class);
        
        // BARU: CRUD Buku
        Route::resource('books', AdminBookController::class);

        // Tambahkan resource lain untuk Admin di sini (Members, Borrowings, Fines)
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