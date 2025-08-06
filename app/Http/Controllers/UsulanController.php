<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usulan;
use App\Models\Unit;
use App\Models\Location; // hanya 1 model Location
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
    $lantais = Location::whereNull('parent_id')->where('level','lantai')->get(); // Lantai = parent utama

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

            // Field tambahan untuk Location dan unit
            'unit_id' => 'required|string|max:255',
            'lantai_id' => 'required|exists:Locations,id',
            'ruang_id' => 'required|exists:Locations,id',
            'sub_ruang_id' => 'required|exists:Locations,id',
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
