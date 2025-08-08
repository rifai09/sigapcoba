<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParameterValuesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parameter_values', function (Blueprint $table) {
            $table->id();
            $table->string('group', 191);
            $table->string('key', 191);
            $table->string('value'); // contoh: 'Menunggu', 'Laki-laki'
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['group', 'key']); // menjamin tidak ada duplikat key di group yang sama
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameter_values');
    }
}
