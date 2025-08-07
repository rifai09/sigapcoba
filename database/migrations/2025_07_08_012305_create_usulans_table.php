<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usulans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang');
            $table->text('spesifikasi');
            $table->text('keterangan')->nullable();
            $table->string('gambar')->nullable();

            $table->integer('jumlah');
            $table->Integer('harga_perkiraan')->nullable();
            $table->string('satuan');

            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('lantai_id')->nullable();
            $table->unsignedBigInteger('ruang_id')->nullable();
            $table->unsignedBigInteger('sub_ruang_id')->nullable();

            // Relasi ke tabel lain
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('lantai_id')->references('id')->on('lokasis')->onDelete('set null');
            $table->foreign('ruang_id')->references('id')->on('lokasis')->onDelete('set null');
            $table->foreign('sub_ruang_id')->references('id')->on('lokasis')->onDelete('set null');


            $table->string('status')->default('menunggu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usulans');
    }
};
