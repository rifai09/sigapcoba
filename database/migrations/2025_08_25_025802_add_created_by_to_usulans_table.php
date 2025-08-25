<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('usulans', function (Blueprint $table) {
            // tambahkan kolom created_by jika belum ada
            if (!Schema::hasColumn('usulans', 'created_by')) {
                $table->foreignId('created_by')
                      ->nullable()
                      ->after('status')
                      ->constrained('users')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void {
        Schema::table('usulans', function (Blueprint $table) {
            if (Schema::hasColumn('usulans', 'created_by')) {
                // drop FK lalu drop kolom
                $table->dropConstrainedForeignId('created_by');
            }
        });
    }
};
