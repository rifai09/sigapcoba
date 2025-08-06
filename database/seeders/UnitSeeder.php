<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            "Instalasi Rawat Jalan",
            "Instalasi Rawat Inap",
            "Instalasi Gawat Darurat",
            "Instalasi Bedah Sentral",
            "Intensive Care Unit (ICU)",
            "Intensive Cardiac Care Unit (ICCU)",
            "Neonatal Intensive Care Unit (NICU)",
            "Instalasi Radiologi",
            "Instalasi Laboratorium Terintegrasi",
            "Instalasi Farmasi",
            "Instalasi Gizi",
            "Instalasi Rekam Medik",
            "Instalasi Pemeliharaan Sarana",
            "Instalasi Penyehatan Lingkungan",
            "Instalasi Central Sterile Supply Department (CSSD)",
            "Unit Pelayanan Pengaduan Masyarakat",
            "Unit Pengelola Sistem Informasi Manajemen Rumah Sakit (SIMRS)",
            "Instansi Pemulasaran Jenazah",
            "Unit Keamanan",
            "Unit Ambulance dan Transportasi"
        ];

        foreach ($units as $nama) {
            Unit::create(['nama' => $nama]);
        }
    }
}

