<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // Jika Anda ingin verifikasi email
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable // Opsi: implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nis_nim',
        'address',
        'phone_number',
        'profile_photo_path',
        'email_verified_at', // Tambahkan jika Anda menggunakan MustVerifyEmail
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Constants for roles
    public const ROLE_ADMIN = 'admin';
    public const ROLE_PETUGAS = 'petugas';
    public const ROLE_ANGGOTA = 'anggota';

    /**
     * Check if the user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Get the borrowings for the user (member).
     */
    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    /**
     * Get the fines for the user (member).
     */
    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    /**
     * Get the borrowings processed by the user (officer/admin).
     */
    public function processedBorrowings()
    {
        return $this->hasMany(Borrowing::class, 'processed_by_user_id');
    }
}