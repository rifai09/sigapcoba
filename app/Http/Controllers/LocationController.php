<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Ambil semua lantai.
     * Return JSON: [{ id: string, nama: string }]
     */
    public function getLantai()
    {
        $lantai = Location::where('level', 'lantai')
            ->orderBy('nama')
            ->get(['id', 'nama'])
            ->map(function ($row) {
                return [
                    'id'   => (string) $row->id, // pastikan string agar konsisten di Select2
                    'nama' => $row->nama,
                ];
            });

        return response()->json($lantai);
    }

    /**
     * Ambil semua ruang berdasarkan parent lantai.
     * @param  int|string $lantai_id
     * Return JSON: [{ id: string, nama: string }]
     */
    public function getRuang($lantai_id)
    {
        $ruang = Location::where('level', 'ruang')
            ->where('parent_id', $lantai_id)
            ->orderBy('nama')
            ->get(['id', 'nama'])
            ->map(function ($row) {
                return [
                    'id'   => (string) $row->id,
                    'nama' => $row->nama,
                ];
            });

        return response()->json($ruang);
    }

    /**
     * Ambil semua sub-ruang berdasarkan parent ruang.
     * Jika kosong, kembalikan 1 item dummy sentinel agar terlihat di Select2:
     *   id   : "__NONE__"  (bukan string kosong supaya tidak dianggap placeholder)
     *   nama : "Tidak ada detail sub ruang"
     *
     * Frontend wajib mengonversi "__NONE__" menjadi '' sebelum submit
     * agar kolom sub_ruang_id tersimpan NULL (nullable).
     *
     * @param  int|string $ruang_id
     * Return JSON: [{ id: string, nama: string }]
     */
    public function getSubRuang($ruang_id)
    {
        $sub = Location::where('level', 'sub_ruang')
            ->where('parent_id', $ruang_id)
            ->orderBy('nama')
            ->get(['id', 'nama'])
            ->map(function ($row) {
                return [
                    'id'   => (string) $row->id,
                    'nama' => $row->nama,
                ];
            });

        if ($sub->isEmpty()) {
            return response()->json([
                ['id' => '__NONE__', 'nama' => 'Tidak ada detail sub ruang']
            ]);
        }

        return response()->json($sub);
    }
}
