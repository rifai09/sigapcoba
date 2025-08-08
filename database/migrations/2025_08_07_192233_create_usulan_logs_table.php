<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsulanLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('usulan_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usulan_id');
            $table->unsignedBigInteger('status_id');   // relasi ke parameter_values
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('urgensi_id')->nullable(); // relasi ke parameter_values
            $table->unsignedBigInteger('user_id');     // siapa yang menyetujui/menolak
            $table->timestamps();

            // Foreign Keys
            $table->foreign('usulan_id')->references('id')->on('usulans')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('parameter_values')->onDelete('restrict');
            $table->foreign('urgensi_id')->references('id')->on('parameter_values')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usulan_logs');
    }
}
