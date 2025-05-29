<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Ganti dengan password yang kuat
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        // Petugas User
        User::create([
            'name' => 'Petugas User',
            'email' => 'petugas@example.com',
            'password' => Hash::make('password'), // Ganti dengan password yang kuat
            'role' => User::ROLE_PETUGAS,
            'email_verified_at' => now(),
        ]);

        // Anggota User 1
        User::create([
            'name' => 'Anggota Satu',
            'email' => 'anggota1@example.com',
            'password' => Hash::make('password'), // Ganti dengan password yang kuat
            'role' => User::ROLE_ANGGOTA,
            'nis_nim' => 'S001',
            'address' => 'Jl. Anggota No. 1',
            'phone_number' => '081234567890',
            'email_verified_at' => now(),
        ]);

        // Anggota User 2
        User::create([
            'name' => 'Anggota Dua',
            'email' => 'anggota2@example.com',
            'password' => Hash::make('password'), // Ganti dengan password yang kuat
            'role' => User::ROLE_ANGGOTA,
            'nis_nim' => 'M002',
            'address' => 'Jl. Mahasiswa No. 2',
            'phone_number' => '081209876543',
            'email_verified_at' => now(),
        ]);

        // Anda bisa menambahkan lebih banyak user atau menggunakan factory
        // User::factory(10)->create(['role' => User::ROLE_ANGGOTA]); // Jika Anda membuat UserFactory
    }
}