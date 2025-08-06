<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run()
    {
        $lantais = [
            'Lantai 1A',
            'Lantai 1B',
            'Lantai 1C',
            'Lantai 2A',
            'Lantai 2B'
        ];

        // Buat lantai
        $lantai_ids = [];
        foreach ($lantais as $lantai) {
            $lantai_ids[] = Location::create([
                'nama' => $lantai,
                'level' => 'lantai',
            ])->id;
        }

        // Daftar nama ruang - pahlawan wanita
        $pahlawanWanita = [
            'Kartini',
            'Cut Nyak Dien',
            'Martha Christina Tiahahu',
            'Dewi Sartika',
            'Rasuna Said',
            'Nyai Ahmad Dahlan',
            'Maria Walanda Maramis',
            'Christina Martha Tiahahu',
            'Fatmawati',
            'Nyai Ageng Serang',
            'Tjut Meutia',
            'R.A. Lasminingrat',
            'Rohana Kudus',
            'Siti Manggopoh'
        ];

        // Buat ruang dan sub ruang per lantai
        foreach ($lantai_ids as $lantai_id) {
            // Ambil 3-4 pahlawan secara acak
            $randomPahlawan = collect($pahlawanWanita)->random(rand(3, 4))->unique();

            foreach ($randomPahlawan as $namaRuang) {
                $ruang = Location::create([
                    'nama' => $namaRuang,
                    'level' => 'ruang',
                    'parent_id' => $lantai_id,
                ]);

                // Buat 2-3 sub_ruang (kamar) per ruang
                $jumlahKamar = rand(2, 3);
                for ($i = 1; $i <= $jumlahKamar; $i++) {
                    Location::create([
                        'nama' => 'Kamar ' . $i,
                        'level' => 'sub_ruang',
                        'parent_id' => $ruang->id,
                    ]);
                }
            }
        }
    }
}
