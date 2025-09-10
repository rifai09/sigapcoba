<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsulanStoreRequest;
use App\Models\Usulan;
use App\Models\Unit;
use App\Models\Location;
use App\Models\User;
use App\Notifications\UsulanNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Response;

class UsulanController extends Controller
{
    /**
     * Form create usulan (role: staf, admin).
     * Mengirimkan dropdown Unit dan Lantai ke view.
     * - Lantai diambil adaptif: cek kolom 'jenis' | 'level' | 'type' | fallback parent_id NULL
     */
    public function create()
    {
        $units = Unit::orderBy('nama')->get(['id', 'nama']);

        // Ambil "lantai" secara adaptif sesuai struktur tabel locations
        if (Schema::hasColumn('locations', 'jenis')) {
            $lantais = Location::where('jenis', 'lantai')->orderBy('nama')->get(['id', 'nama']);
        } elseif (Schema::hasColumn('locations', 'level')) {
            $lantais = Location::where('level', 'lantai')->orderBy('nama')->get(['id', 'nama']);
        } elseif (Schema::hasColumn('locations', 'type')) {
            $lantais = Location::where('type', 'lantai')->orderBy('nama')->get(['id', 'nama']);
        } else {
            // Fallback umum: asumsikan root (lantai) adalah node tanpa parent
            $lantais = Location::whereNull('parent_id')->orderBy('nama')->get(['id', 'nama']);
        }

        return view('usulan.create', compact('units', 'lantais'));
    }

    /**
     * Simpan usulan baru dari staf.
     * - Validasi: UsulanStoreRequest (wajib: alasan_pengusulan)
     * - Status awal: menunggu_kepala_unit
     * - Set created_by: id user yang login
     * - Notifikasi: kirim ke semua user role 'kepala_unit'
     */
    public function store(UsulanStoreRequest $request)
    {
        $data = $request->validated();

        // Upload gambar (opsional)
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('usulan', 'public');
        }

        // Hitung total_perkiraan jika ada harga
        if (!empty($data['harga_perkiraan'])) {
            $data['total_perkiraan'] = ((int) ($data['jumlah'] ?? 0)) * ((int) $data['harga_perkiraan']);
        }

        // Status awal tiga level + pencatat pembuat
        $data['status']     = 'menunggu_kepala_unit';
        $data['created_by'] = Auth::id();

        DB::transaction(function () use ($data) {
            $usulan = Usulan::create($data);

            // Kirim notifikasi ke semua Kepala Unit
            $kepalaUnits = User::where('role', 'kepala_unit')->get();
            foreach ($kepalaUnits as $user) {
                $user->notify(new UsulanNotification(
                    usulanId:   $usulan->id,
                    judul:      'Usulan baru menunggu Kepala Unit',
                    pesan:      'Usulan "' . $usulan->nama_barang . '" menunggu persetujuan Anda.',
                    statusBaru: 'menunggu_kepala_unit',
                    byUserId:   Auth::id(),
                    byUserName: Auth::user()->name
                ));
            }
        });

        return redirect()
            ->route('home')
            ->with('success', 'Usulan berhasil dibuat dan dikirim ke Kepala Unit.');
    }

    /**
     * Tampilkan riwayat usulan (untuk modal di dashboard).
     * Aturan akses:
     * - Staf: hanya boleh melihat usulannya sendiri (created_by == auth()->id()).
     * - Approver/Admin: kepala_unit / katimker / kabid / admin boleh melihat semua sesuai kewenangan umum.
     */
    public function riwayat($id, Request $request)
    {
        $user = Auth::user();

        $usulan = Usulan::with([
                'unit:id,nama',
                'lantai:id,nama',
                'ruang:id,nama',
                'subRuang:id,nama',
                'approvalLogs' => function ($q) {
                    $q->orderBy('created_at');
                },
                'approvalLogs.approver:id,name'
            ])
            ->findOrFail($id);

        // Cek role staf
        $isStaf = method_exists($user, 'hasRole')
            ? $user->hasRole('staf')
            : (strtolower($user->role ?? '') === 'staf');

        if ($isStaf && (int)$usulan->created_by !== (int)$user->id) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tidak berwenang melihat riwayat usulan ini.');
        }

        if (!$isStaf) {
            $allowed = false;
            if (method_exists($user, 'hasRole')) {
                $allowed = $user->hasRole('kepala_unit')
                    || $user->hasRole('katimker')
                    || $user->hasRole('kabid')
                    || $user->hasRole('admin');
            } else {
                $role = strtolower((string)($user->role ?? ''));
                $allowed = in_array($role, ['kepala_unit','katimker','kabid','admin'], true);
            }
            if (!$allowed) {
                abort(Response::HTTP_FORBIDDEN, 'Anda tidak berwenang melihat riwayat usulan ini.');
            }
        }

        return view('usulan.partials.riwayat', [
            'usulan' => $usulan,
            'logs'   => $usulan->approvalLogs,
        ]);
    }
}
