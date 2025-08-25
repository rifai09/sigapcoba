<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usulans', function (Blueprint $table) {
            $table->id();

            $table->string('nama_barang');
            $table->text('spesifikasi');
            $table->text('keterangan')->nullable();
            $table->string('gambar')->nullable();

            $table->unsignedBigInteger('jumlah');
            $table->string('satuan');

            $table->bigInteger('harga_perkiraan')->nullable();

            // relasi
            $table->foreignId('unit_id')->constrained('units');
            $table->foreignId('lantai_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('ruang_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('sub_ruang_id')->nullable()->constrained('locations')->nullOnDelete();

            $table->string('status')->default('menunggu');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usulans');
    }
};
