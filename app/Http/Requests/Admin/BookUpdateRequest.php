<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\Rule;

class BookUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole(User::ROLE_ADMIN);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $bookId = $this->route('book') ? $this->route('book')->id : null;

        return [
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'author' => 'required|string|max:255',
            'publisher' => 'required|string|max:255',
            'publication_year' => 'required|digits:4|integer|min:1500|max:'.(date('Y')),
            'isbn' => [
                'required',
                'string',
                'max:20',
                Rule::unique('books', 'isbn')->ignore($bookId),
            ],
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string|max:2000',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Sampul opsional saat update
        ];
    }
     /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul buku wajib diisi.',
            'category_id.required' => 'Kategori buku wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'author.required' => 'Nama penulis wajib diisi.',
            'publisher.required' => 'Nama penerbit wajib diisi.',
            'publication_year.required' => 'Tahun terbit wajib diisi.',
            'publication_year.digits' => 'Tahun terbit harus 4 digit.',
            'publication_year.min' => 'Tahun terbit minimal 1500.',
            'publication_year.max' => 'Tahun terbit tidak boleh melebihi tahun sekarang.',
            'isbn.required' => 'ISBN wajib diisi.',
            'isbn.unique' => 'ISBN sudah terdaftar.',
            'stock_quantity.required' => 'Jumlah stok wajib diisi.',
            'stock_quantity.min' => 'Jumlah stok tidak boleh kurang dari 0.',
            'cover_image.image' => 'Sampul buku harus berupa gambar.',
            'cover_image.mimes' => 'Format sampul buku yang diizinkan: jpeg, png, jpg, gif, webp.',
            'cover_image.max' => 'Ukuran sampul buku maksimal 2MB.',
        ];
    }
}