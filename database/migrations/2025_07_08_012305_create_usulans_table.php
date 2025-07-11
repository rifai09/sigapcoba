<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('usulans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang');
            $table->text('spesifikasi');
            $table->text('keterangan')->nullable();
            $table->string('gambar')->nullable();

            $table->integer('jumlah');
            $table->string('satuan');
            $table->bigInteger('harga_pagu');
            $table->bigInteger('perkiraan_harga');

            // Penjual 1
            $table->string('penjual_1')->nullable();
            $table->bigInteger('harga_penjual_1')->nullable();
            $table->string('link_penjual_1')->nullable();

            // Penjual 2
            $table->string('penjual_2')->nullable();
            $table->bigInteger('harga_penjual_2')->nullable();
            $table->string('link_penjual_2')->nullable();

            // Penjual 3
            $table->string('penjual_3')->nullable();
            $table->bigInteger('harga_penjual_3')->nullable();
            $table->string('link_penjual_3')->nullable();

            $table->string('status')->default('menunggu'); // default status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usulans');
    }
};
