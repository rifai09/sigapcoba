<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usulan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UsulanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        return view('usulan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'spesifikasi' => 'required|string',
            'keterangan' => 'nullable|string',
            'gambar' => 'nullable|image|max:2048',
            'jumlah' => 'required|numeric|min:1',
            'satuan' => 'required|string',
            'harga_pagu' => 'required|numeric|min:0',
            'perkiraan_harga' => 'required|numeric|min:0',
            'penjual_1' => 'nullable|string',
            'harga_penjual_1' => 'nullable|numeric|min:0',
            'link_penjual_1' => 'nullable|url',
            'penjual_2' => 'nullable|string',
            'harga_penjual_2' => 'nullable|numeric|min:0',
            'link_penjual_2' => 'nullable|url',
            'penjual_3' => 'nullable|string',
            'harga_penjual_3' => 'nullable|numeric|min:0',
            'link_penjual_3' => 'nullable|url',
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
