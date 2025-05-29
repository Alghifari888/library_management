<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\BorrowingController as AdminBorrowingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user->hasRole(\App\Models\User::ROLE_ADMIN)) {
        // Anda bisa mengarahkan ke dashboard admin spesifik jika ada
        // return redirect()->route('admin.dashboard'); 
        return view('dashboard', ['userRole' => 'Admin']); 
    } elseif ($user->hasRole(\App\Models\User::ROLE_PETUGAS)) {
        // return redirect()->route('officer.dashboard');
         return view('dashboard', ['userRole' => 'Petugas']);
    } elseif ($user->hasRole(\App\Models\User::ROLE_ANGGOTA)) {
        // return redirect()->route('member.dashboard');
         return view('dashboard', ['userRole' => 'Anggota']);
    }
    return view('dashboard'); // Fallback dashboard
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Grup Rute untuk Admin dan Petugas
// Grup Rute untuk Admin dan Petugas
Route::middleware(['auth', 'role:' . \App\Models\User::ROLE_ADMIN . ',' . \App\Models\User::ROLE_PETUGAS])
    ->prefix('admin') 
    ->name('admin.')
    ->group(function () {
        
        // Khusus Admin
        Route::resource('categories', AdminCategoryController::class)->middleware('role:' . \App\Models\User::ROLE_ADMIN);
        Route::resource('books', AdminBookController::class)->middleware('role:' . \App\Models\User::ROLE_ADMIN);
        Route::resource('members', AdminMemberController::class)->middleware('role:' . \App\Models\User::ROLE_ADMIN);

        // Untuk Admin & Petugas
        Route::get('borrowings', [AdminBorrowingController::class, 'index'])->name('borrowings.index');
        Route::get('borrowings/create', [AdminBorrowingController::class, 'create'])->name('borrowings.create');
        Route::post('borrowings', [AdminBorrowingController::class, 'store'])->name('borrowings.store');
        
        // BARU: Route untuk menandai buku telah dikembalikan
        Route::patch('borrowings/{borrowing}/return', [AdminBorrowingController::class, 'returnBook'])->name('borrowings.return');
});

// Anda bisa menambahkan grup route terpisah untuk Petugas jika ada halaman khusus Petugas
// Route::middleware(['auth', 'role:' . \App\Models\User::ROLE_PETUGAS])
//     ->prefix('officer')
//     ->name('officer.')
//     ->group(function () {
//         // Route::get('/dashboard', [SomeOfficerController::class, 'dashboard'])->name('dashboard');
//     });


require __DIR__.'/auth.php';