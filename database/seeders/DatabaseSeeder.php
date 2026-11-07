<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed User Super Admin
        User::create([
            'name' => 'Admin PSY',
            'email' => 'mnur0003@student.ciputra.ac.id',
            'role' => 'admin',
        ]);

        // 2. Seed Contoh Dosen
        User::create([
            'name' => 'Ce Stevani, M.Psi.',
            'email' => 'muhalifakbar67@gmail.com',
            'role' => 'dosen',
        ]);

        // 3. Seed Contoh Mahasiswa
        User::create([
            'name' => 'Vincent',
            'email' => 'vincenthalim02@student.ciputra.ac.id',
            'role' => 'mahasiswa',
            'nim' => '0706012310001',
            'angkatan' => 2023,
        ]);

        // 4. Seed Kategori Laporan Kasus Default Psikologi
        $categories = [
            'Psikologi Perkembangan (Anak & Remaja)',
            'Psikologi Klinis (Dewasa & Lansia)',
            'Psikologi Industri & Organisasi (PIO)',
            'Psikologi Pendidikan',
            'Psikologi Sosial & Komunitas',
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
                'is_hidden' => false,
            ]);
        }
    }
}