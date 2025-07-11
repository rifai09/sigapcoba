<?php

namespace App\Http\Controllers;

use App\Models\Usulan;
use Illuminate\Http\Request;

class PersetujuanController extends Controller
{
    public function index()
    {
        $usulans = Usulan::orderBy('created_at', 'desc')->get();
        return view('persetujuan.index', compact('usulans'));
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