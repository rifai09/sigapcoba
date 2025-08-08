<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usulan;
use App\Models\Unit;
use App\Models\Location; // hanya 1 model Location
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\LogUsulan;
use App\Models\ParameterValues;
use Illuminate\Support\Facades\Auth;

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
    $lantais = Location::whereNull('parent_id')->get(); // Lantai = parent utama

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
            'harga_perkiraan' => 'nullable|numeric|min:0',
            'satuan' => 'required|string',

            // Field tambahan untuk Location dan unit
            'unit_id' => 'required|string|max:255',
            'lantai_id' => 'required',
            'ruang_id' => 'required',
            'sub_ruang_id' => 'required',
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
    public function setujui(Request $request)
{
    $request->validate([
        'usulan_id' => 'required|exists:usulan,id',
        'keterangan' => 'required|string',
        'urgensi' => 'required|in:urgen,not_urgent',
    ]);

    DB::beginTransaction();
    try {
        // Update data di tabel usulan
        $usulan = Usulan::findOrFail($request->usulan_id);
        $usulan->status = 'disetujui';
        $usulan->keterangan_persetujuan = $request->keterangan;
        $usulan->urgensi = $request->urgensi;
        $usulan->approved_by =Auth::user()->id;
        $usulan->approved_at = now();
        $usulan->save();

        // Simpan log
        LogUsulan::create([
            'usulan_id' => $usulan->id,
            'user_id' => Auth::user()->id,
            'action' => 'disetujui',
            'keterangan' => $request->keterangan,
            'created_at' => now(),
        ]);

        DB::commit();

        return response()->json(['message' => 'Usulan berhasil disetujui.']);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Gagal menyetujui usulan.'], 500);
    }
}
    
}
