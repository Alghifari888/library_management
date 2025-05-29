<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\BorrowingController as AdminBorrowingController;
use App\Http\Controllers\Admin\FineController as AdminFineController; // BARU: Import FineController

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
        return view('dashboard', ['userRole' => 'Admin']); 
    } elseif ($user->hasRole(\App\Models\User::ROLE_PETUGAS)) {
         return view('dashboard', ['userRole' => 'Petugas']);
    } elseif ($user->hasRole(\App\Models\User::ROLE_ANGGOTA)) {
         return view('dashboard', ['userRole' => 'Anggota']);
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Grup Rute untuk Admin dan Petugas
Route::middleware(['auth', 'role:' . \App\Models\User::ROLE_ADMIN . ',' . \App\Models\User::ROLE_PETUGAS])
    ->prefix('admin') 
    ->name('admin.')
    ->group(function () {
        
        // Khusus Admin
        Route::resource('categories', AdminCategoryController::class)->middleware('role:' . \App\Models\User::ROLE_ADMIN);
        Route::resource('books', AdminBookController::class)->middleware('role:' . \App\Models\User::ROLE_ADMIN);
        Route::resource('members', AdminMemberController::class)->middleware('role:' . \App\Models\User::ROLE_ADMIN);

        // Untuk Admin & Petugas (Manajemen Peminjaman)
        Route::get('borrowings', [AdminBorrowingController::class, 'index'])->name('borrowings.index');
        Route::get('borrowings/create', [AdminBorrowingController::class, 'create'])->name('borrowings.create');
        Route::post('borrowings', [AdminBorrowingController::class, 'store'])->name('borrowings.store');
        Route::patch('borrowings/{borrowing}/return', [AdminBorrowingController::class, 'returnBook'])->name('borrowings.return');

        // BARU: Untuk Admin & Petugas (Manajemen Denda)
        Route::get('fines', [AdminFineController::class, 'index'])->name('fines.index');
        Route::patch('fines/{fine}/pay', [AdminFineController::class, 'markAsPaid'])->name('fines.pay');
});

require __DIR__.'/auth.php';