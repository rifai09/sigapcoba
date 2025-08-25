<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('usulans', function (Blueprint $table) {
            // --- Input & perhitungan ---
            if (!Schema::hasColumn('usulans', 'persediaan_saat_ini')) {
                $table->integer('persediaan_saat_ini')->default(0)->after('jumlah');
            }

            if (!Schema::hasColumn('usulans', 'total_perkiraan')) {
                $table->bigInteger('total_perkiraan')->nullable()->after('harga_perkiraan');
            }

            // --- Alasan Pengusulan (diisi staf saat create) ---
            if (!Schema::hasColumn('usulans', 'alasan_pengusulan')) {
                $table->text('alasan_pengusulan')->after('spesifikasi');
            }

            // --- Status global (set default ke tahap pertama: kepala unit) ---
            if (Schema::hasColumn('usulans', 'status')) {
                DB::statement("ALTER TABLE usulans ALTER COLUMN status SET DEFAULT 'menunggu_kepala_unit'");
            } else {
                $table->string('status', 30)->default('menunggu_kepala_unit');
            }

            // --- Jejak approval Kepala Unit (ringkasan simetris) ---
            if (!Schema::hasColumn('usulans', 'kepala_unit_status')) {
                $table->string('kepala_unit_status', 10)->default('pending')->after('status');
            }
            if (!Schema::hasColumn('usulans', 'kepala_unit_priority')) {
                $table->string('kepala_unit_priority', 10)->nullable()->after('kepala_unit_status');
            }
            if (!Schema::hasColumn('usulans', 'kepala_unit_note')) {
                $table->text('kepala_unit_note')->nullable()->after('kepala_unit_priority');
            }
            if (!Schema::hasColumn('usulans', 'kepala_unit_by')) {
                $table->foreignId('kepala_unit_by')->nullable()->constrained('users')->nullOnDelete()->after('kepala_unit_note');
            }
            if (!Schema::hasColumn('usulans', 'kepala_unit_at')) {
                $table->timestamp('kepala_unit_at')->nullable()->after('kepala_unit_by');
            }

            // --- Jejak approval Katimker (tetap) ---
            if (!Schema::hasColumn('usulans', 'katimker_status')) {
                $table->string('katimker_status', 10)->default('pending')->after('kepala_unit_at');
            }
            if (!Schema::hasColumn('usulans', 'katimker_priority')) {
                $table->string('katimker_priority', 10)->nullable()->after('katimker_status');
            }
            if (!Schema::hasColumn('usulans', 'katimker_note')) {
                $table->text('katimker_note')->nullable()->after('katimker_priority');
            }
            if (!Schema::hasColumn('usulans', 'katimker_by')) {
                $table->foreignId('katimker_by')->nullable()->constrained('users')->nullOnDelete()->after('katimker_note');
            }
            if (!Schema::hasColumn('usulans', 'katimker_at')) {
                $table->timestamp('katimker_at')->nullable()->after('katimker_by');
            }

            // --- Jejak approval Kabid (tetap) ---
            if (!Schema::hasColumn('usulans', 'kabid_status')) {
                $table->string('kabid_status', 10)->default('pending')->after('katimker_at');
            }
            if (!Schema::hasColumn('usulans', 'kabid_priority')) {
                $table->string('kabid_priority', 10)->nullable()->after('kabid_status');
            }
            if (!Schema::hasColumn('usulans', 'kabid_note')) {
                $table->text('kabid_note')->nullable()->after('kabid_priority');
            }
            if (!Schema::hasColumn('usulans', 'kabid_by')) {
                $table->foreignId('kabid_by')->nullable()->constrained('users')->nullOnDelete()->after('kabid_note');
            }
            if (!Schema::hasColumn('usulans', 'kabid_at')) {
                $table->timestamp('kabid_at')->nullable()->after('kabid_by');
            }

            // --- Prioritas final (diisi kabid saat approve) ---
            if (!Schema::hasColumn('usulans', 'prioritas_final')) {
                $table->string('prioritas_final', 10)->nullable()->after('kabid_at');
            }
        });

        // --- Index (pakai IF NOT EXISTS via statement untuk lintas driver) ---
        try { DB::statement("CREATE INDEX IF NOT EXISTS usulans_status_idx ON usulans(status)"); } catch (\Throwable $e) {}
        try { DB::statement("CREATE INDEX IF NOT EXISTS usulans_kepala_unit_idx ON usulans(kepala_unit_by, kepala_unit_status)"); } catch (\Throwable $e) {}
        try { DB::statement("CREATE INDEX IF NOT EXISTS usulans_katimker_idx ON usulans(katimker_by, katimker_status)"); } catch (\Throwable $e) {}
        try { DB::statement("CREATE INDEX IF NOT EXISTS usulans_kabid_idx ON usulans(kabid_by, kabid_status)"); } catch (\Throwable $e) {}
        try { DB::statement("CREATE INDEX IF NOT EXISTS usulans_unit_idx ON usulans(unit_id)"); } catch (\Throwable $e) {}

        // --- Normalisasi data lama (map ke tahap pertama tiga-level) ---
        DB::statement("
            UPDATE usulans
            SET status = CASE
                WHEN lower(status) IN ('menunggu','menunggu_kepala_unit') THEN 'menunggu_kepala_unit'
                WHEN lower(status) IN ('menunggu_katimker') THEN 'menunggu_katimker'
                WHEN lower(status) IN ('menunggu_kabid') THEN 'menunggu_kabid'
                WHEN lower(status) = 'disetujui' THEN 'disetujui'
                WHEN lower(status) = 'ditolak' THEN 'ditolak'
                ELSE 'menunggu_kepala_unit'
            END
        ");

        DB::statement("
            UPDATE usulans
            SET total_perkiraan = (CASE
                WHEN harga_perkiraan IS NOT NULL THEN (jumlah * harga_perkiraan)
                ELSE NULL
            END)
            WHERE total_perkiraan IS NULL
        ");

        // --- CHECK constraints khusus PostgreSQL (tanpa IF NOT EXISTS; pakai try/catch) ---
        if (config('database.default') === 'pgsql') {
            // status ringkasan per level
            try { DB::statement("ALTER TABLE usulans ADD CONSTRAINT chk_usulans_kepala_unit_status CHECK (kepala_unit_status IN ('pending','approved','rejected'))"); } catch (\Throwable $e) {}
            try { DB::statement("ALTER TABLE usulans ADD CONSTRAINT chk_usulans_katimker_status CHECK (katimker_status IN ('pending','approved','rejected'))"); } catch (\Throwable $e) {}
            try { DB::statement("ALTER TABLE usulans ADD CONSTRAINT chk_usulans_kabid_status CHECK (kabid_status IN ('pending','approved','rejected'))"); } catch (\Throwable $e) {}

            // status global tiga level
            try { DB::statement("ALTER TABLE usulans ADD CONSTRAINT chk_usulans_status CHECK (status IN ('menunggu_kepala_unit','menunggu_katimker','menunggu_kabid','disetujui','ditolak'))"); } catch (\Throwable $e) {}

            // prioritas
            try { DB::statement("ALTER TABLE usulans ADD CONSTRAINT chk_usulans_prioritas_final CHECK (prioritas_final IS NULL OR prioritas_final IN ('urgent','normal'))"); } catch (\Throwable $e) {}
            try { DB::statement("ALTER TABLE usulans ADD CONSTRAINT chk_usulans_prioritas_kepala_unit CHECK (kepala_unit_priority IS NULL OR kepala_unit_priority IN ('urgent','normal'))"); } catch (\Throwable $e) {}
            try { DB::statement("ALTER TABLE usulans ADD CONSTRAINT chk_usulans_prioritas_katimker CHECK (katimker_priority IS NULL OR katimker_priority IN ('urgent','normal'))"); } catch (\Throwable $e) {}
            try { DB::statement("ALTER TABLE usulans ADD CONSTRAINT chk_usulans_prioritas_kabid CHECK (kabid_priority IS NULL OR kabid_priority IN ('urgent','normal'))"); } catch (\Throwable $e) {}

            // non-negatif
            try { DB::statement("ALTER TABLE usulans ADD CONSTRAINT chk_usulans_persediaan_nonneg CHECK (persediaan_saat_ini >= 0)"); } catch (\Throwable $e) {}
        }
    }

    public function down(): void {
        // Drop index dulu
        try { DB::statement("DROP INDEX IF EXISTS usulans_status_idx"); } catch (\Throwable $e) {}
        try { DB::statement("DROP INDEX IF EXISTS usulans_kepala_unit_idx"); } catch (\Throwable $e) {}
        try { DB::statement("DROP INDEX IF EXISTS usulans_katimker_idx"); } catch (\Throwable $e) {}
        try { DB::statement("DROP INDEX IF EXISTS usulans_kabid_idx"); } catch (\Throwable $e) {}
        try { DB::statement("DROP INDEX IF EXISTS usulans_unit_idx"); } catch (\Throwable $e) {}

        Schema::table('usulans', function (Blueprint $table) {
            $dropCols = [
                'prioritas_final',
                'kabid_at','kabid_by','kabid_note','kabid_priority','kabid_status',
                'katimker_at','katimker_by','katimker_note','katimker_priority','katimker_status',
                'kepala_unit_at','kepala_unit_by','kepala_unit_note','kepala_unit_priority','kepala_unit_status',
                'alasan_pengusulan',
                'total_perkiraan','persediaan_saat_ini',
            ];

            $existing = array_filter($dropCols, fn($c) => Schema::hasColumn('usulans', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }

            if (Schema::hasColumn('usulans', 'status')) {
                try { DB::statement("ALTER TABLE usulans ALTER COLUMN status SET DEFAULT 'menunggu_katimker'"); } catch (\Throwable $e) {}
            }
        });
    }
};
