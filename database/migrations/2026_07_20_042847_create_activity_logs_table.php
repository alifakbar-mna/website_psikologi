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
        Schema::create('activity_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Pelaku aktivitas
            $blueprint->string('action'); // Contoh: 'GRANT_ACCESS', 'REVOKE_ROLE', 'DOWNLOAD_PDF'
            $blueprint->text('description'); // Contoh: 'Admin mencabut akses Dosen dari User B' atau 'User A mendownload Dokumen X'
            $blueprint->string('ip_address', 45)->nullable();
            $blueprint->text('user_agent')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
