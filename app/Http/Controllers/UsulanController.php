<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usulan;
use App\Models\Unit;
use App\Models\Lokasi; // hanya 1 model lokasi
use Illuminate\Support\Facades\Storage;

class UsulanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ✅ FORM USULAN
    public function create()
{
    $units = Unit::all();
    $lantais = Lokasi::whereNull('parent_id')->get(); // Lantai = parent utama

    return view('usulan.create', compact('units', 'lantais'));
}


    // ✅ SIMPAN USULAN
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'spesifikasi' => 'required|string',
            'keterangan' => 'nullable|string',
            'gambar' => 'nullable|image|max:2048',
            'jumlah' => 'required|numeric|min:1',
            'satuan' => 'required|string',

            // Field tambahan untuk lokasi dan unit
            'unit_pengusul' => 'required|string|max:255',
            'lantai_id' => 'required|exists:lokasis,id',
            'ruang_id' => 'required|exists:lokasis,id',
            'sub_ruang_id' => 'required|exists:lokasis,id',
        ]);

        // Upload gambar jika ada
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('gambar_usulan', 'public');
            $validated['gambar'] = $path;
        }

        $validated['status'] = 'menunggu';

        Usulan::create($validated);

        return redirect()->route('usulan.create')->with('success', 'Usulan berhasil dikirim.');
    }
}
