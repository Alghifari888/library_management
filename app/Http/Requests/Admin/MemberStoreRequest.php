<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Pastikan model User di-import
use Illuminate\Validation\Rules\Password;

class MemberStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Hanya admin yang boleh membuat anggota baru
        return Auth::check() && Auth::user()->hasRole(User::ROLE_ADMIN);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()], // Menggunakan aturan password default Laravel
            'nis_nim' => 'nullable|string|max:50|unique:users,nis_nim',
            'address' => 'nullable|string|max:1000',
            'phone_number' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Max 2MB
            // 'role' akan di-set di controller menjadi 'anggota'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama anggota wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'nis_nim.unique' => 'NIS/NIM sudah terdaftar.',
            'nis_nim.max' => 'NIS/NIM maksimal 50 karakter.',
            'profile_photo.image' => 'Foto profil harus berupa gambar.',
            'profile_photo.mimes' => 'Format foto profil yang diizinkan: jpeg, png, jpg, gif, webp.',
            'profile_photo.max' => 'Ukuran foto profil maksimal 2MB.',
        ];
    }
}