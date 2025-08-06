<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function getLantai()
    {
        $lantai = Lokasi::where('level', 'lantai')->get();
        return response()->json($lantai);
    }

    public function getRuang($lantai_id)
    {
        dd ($lantai_id);
        $ruang = Lokasi::where('level', 'ruang')->where('parent_id', $lantai_id)->get();
        return response()->json($ruang);
    }

    public function getSubRuang($ruang_id)
    {
        $sub = Lokasi::where('level', 'sub_ruang')->where('parent_id', $ruang_id)->get();
        return response()->json($sub);
    }
}
