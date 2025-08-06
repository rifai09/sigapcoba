<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function getLantai()
    {
        $lantai = Location::where('level', 'lantai')->get();
        return response()->json($lantai);
    }

    public function getRuang($lantai_id)
    {
       
        $ruang = Location::where('level', 'ruang')->where('parent_id', $lantai_id)->get();
        return response()->json($ruang);
    }

    public function getSubRuang($ruang_id)
    {
        $sub = Location::where('level', 'sub_ruang')->where('parent_id', $ruang_id)->get();
        return response()->json($sub);
    }
}
