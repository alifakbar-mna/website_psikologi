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
        Schema::create('documents', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('title');
            $blueprint->string('pdf_path'); // Menyimpan path file PDF di storage
            $blueprint->year('tahun');
            $blueprint->string('dosen_pembimbing');
            $blueprint->year('angkatan_pembuat'); // Angkatan kakak tingkat / pembuat dokumen
            $blueprint->enum('status', ['open', 'hide'])->default('open');
            
            // Foreign Keys
            $blueprint->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $blueprint->foreignId('uploaded_by')->constrained('users')->onDelete('cascade'); // Siapa yang upload
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
