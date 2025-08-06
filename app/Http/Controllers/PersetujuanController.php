<?php

namespace App\Http\Controllers;

use App\Models\Usulan;
use Illuminate\Http\Request;
use App\Models\Location;
use Yajra\DataTables\Facades\DataTables;


class PersetujuanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
        $data = Usulan::with(['unit', 'lantai', 'ruang', 'subRuang'])->latest();

        return DataTables::of($data)
            ->addColumn('unit', fn($row) => $row->unit->nama ?? '-')
            ->addColumn('lantai', fn($row) => $row->lantai->nama ?? '-')
            ->addColumn('ruang', fn($row) => $row->ruang->nama ?? '-')
            ->addColumn('sub_ruang', fn($row) => $row->subRuang->nama ?? '-')
            ->addColumn('aksi', function ($row) {
                return view('persetujuan._action', compact('row'))->render();
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }
     
        return view('persetujuan.index');
    }


    public function setujui($id)
    {
        $usulan = Usulan::findOrFail($id);
        $usulan->status = 'Disetujui';
        $usulan->save();

        return redirect()->back()->with('success', 'Usulan disetujui.');
    }

    public function tolak($id)
    {
        $usulan = Usulan::findOrFail($id);
        $usulan->status = 'Ditolak';
        $usulan->save();

        return redirect()->back()->with('success', 'Usulan ditolak.');
    }
}
