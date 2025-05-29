<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Pastikan model User di-import

class BorrowingStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admin atau Petugas boleh membuat transaksi peminjaman
        $user = Auth::user();
        return $user && ($user->hasRole(User::ROLE_ADMIN) || $user->hasRole(User::ROLE_PETUGAS));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id', // Memastikan user_id adalah anggota yang valid
            'book_id' => 'required|exists:books,id',
            'borrowed_at' => 'required|date',
            'due_at' => 'required|date|after_or_equal:borrowed_at',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Anggota peminjam wajib dipilih.',
            'user_id.exists' => 'Anggota yang dipilih tidak valid.',
            'book_id.required' => 'Buku yang dipinjam wajib dipilih.',
            'book_id.exists' => 'Buku yang dipilih tidak valid.',
            'borrowed_at.required' => 'Tanggal pinjam wajib diisi.',
            'borrowed_at.date' => 'Format tanggal pinjam tidak valid.',
            'due_at.required' => 'Tanggal jatuh tempo wajib diisi.',
            'due_at.date' => 'Format tanggal jatuh tempo tidak valid.',
            'due_at.after_or_equal' => 'Tanggal jatuh tempo harus setelah atau sama dengan tanggal pinjam.',
        ];
    }
}