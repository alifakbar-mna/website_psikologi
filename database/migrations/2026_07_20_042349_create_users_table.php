<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Informasi dasar pengguna
            $table->string('name');
            $table->string('email')->unique();

            // Hak akses pengguna
            $table->enum('role', ['admin', 'dosen', 'mahasiswa'])
                  ->default('mahasiswa');

            // Khusus mahasiswa
            $table->string('nim')->nullable()->unique();
            $table->year('angkatan')->nullable();

            // Registrasi mahasiswa oleh admin
            $table->boolean('is_registered_by_admin')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};