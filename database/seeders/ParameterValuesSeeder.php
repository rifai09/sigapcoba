<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParameterValuesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('parameter_values')->insert([
            [
                'group' => 'urgency',
                'key' => 'urgen',
                'value' => 'Urgent',
                'description' => 'Butuh penanganan segera',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'urgency',
                'key' => 'not_urgent',
                'value' => 'Not Urgent',
                'description' => 'Tidak butuh penanganan segera',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'status_verifikasi',
                'key' => 'diterima',
                'value' => 'Diterima',
                'description' => 'Usulan disetujui',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'status_verifikasi',
                'key' => 'ditolak',
                'value' => 'Ditolak',
                'description' => 'Usulan ditolak',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
