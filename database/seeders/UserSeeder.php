<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'nama'          => 'Pemilik Kos (Super Admin)',
            'email'         => 'administrator@example.com',
            'password'      => bcrypt('superadmin123'),
            'nomor_telepon' => '081111111111',
            'role'          => 'administrator',
            'status_akun'   => 'aktif',
        ]);

        User::create([
            'nama'          => 'Penjaga Kos (Admin)',
            'email'         => 'admin@example.com',
            'password'      => bcrypt('admin123'),
            'nomor_telepon' => '081234567890',
            'role'          => 'admin',
            'status_akun'   => 'aktif',
        ]);

        User::create([
            'nama'          => 'Budi Santoso',
            'email'         => 'penyewa@example.com',
            'password'      => bcrypt('penyewa123'),
            'nomor_telepon' => '081298765432',
            'role'          => 'penyewa',
            'status_akun'   => 'aktif',
        ]);
    }
}
