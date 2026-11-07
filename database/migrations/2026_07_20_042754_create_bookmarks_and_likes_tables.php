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
        Schema::create('bookmarks', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $blueprint->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $blueprint->timestamps();
            
            $blueprint->unique(['user_id', 'document_id']); // Mencegah double bookmark dokumen yang sama
        });

        // Tabel Like
        Schema::create('likes', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $blueprint->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $blueprint->timestamps();

            $blueprint->unique(['user_id', 'document_id']); // Mencegah double like dokumen yang sama
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('likes');
    }
};
