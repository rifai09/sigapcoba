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
       Schema::create('lokasis', function (Blueprint $table) {
        $table->id();
        $table->string('nama'); // Nama lantai, ruang, atau sub ruang
        $table->enum('level', ['lantai', 'ruang', 'sub_ruang']); // Jenis level
        $table->foreignId('parent_id')->nullable()->constrained('lokasis')->onDelete('cascade'); // Induknya
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasis');
    }
};
