<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LocationExcelSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/public/lokasi.xlsx');
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // skip header

            if (empty($row[0]) && empty($row[1]) && (empty($row[2]) || !isset($row[2]))) {
                continue; // skip baris kosong
            }
            $namaLantai = trim($row[0]);
            $namaRuang = trim($row[1]) ?: 'Umum';
            $namaSubRuang = isset($row[2]) ? trim($row[2]) : null;

            // Lantai
            $lantai = Location::firstOrCreate([
                'nama' => $namaLantai,
                'level' => 'lantai',
                'parent_id' => null
            ]);

            // Ruang
            $ruang = Location::firstOrCreate([
                'nama' => $namaRuang,
                'level' => 'ruang',
                'parent_id' => $lantai->id
            ]);

            // Sub Ruang (jika ada)
            if (!empty($namaSubRuang)) {
                Location::firstOrCreate([
                    'nama' => $namaSubRuang,
                    'level' => 'sub_ruang',
                    'parent_id' => $ruang->id
                ]);
            }
        }
    }
}
