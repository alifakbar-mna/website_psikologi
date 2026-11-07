<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('temporary_permissions', function (Blueprint $blueprint) {
            $blueprint->id();
            
            // Target Penerima Akses (Bisa pilih salah satu opsi di bawah via logic aplikasi)
            $blueprint->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Opsi 1: Spesifik 1 Mahasiswa
            $blueprint->year('target_angkatan')->nullable(); // Opsi 2: Per Angkatan
            $blueprint->boolean('is_all_students')->default(false); // Opsi 3: Semua Mahasiswa langsung
            
            // Target Objek Dokumen (Nullable jika izin bersifat global seperti izin login/baca umum)
            $blueprint->foreignId('document_id')->nullable()->constrained('documents')->onDelete('cascade');
            
            // Checkbox Akses yang Diberikan
            $blueprint->boolean('can_read')->default(false);
            $blueprint->boolean('can_download')->default(false);
            $blueprint->boolean('can_upload')->default(false);
            
            // Masa Berlaku Akses
            $blueprint->dateTime('expired_at');
            
            $blueprint->foreignId('granted_by')->constrained('users')->onDelete('cascade'); // Admin/Dosen pemberi akses
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_permissions');
    }
};
