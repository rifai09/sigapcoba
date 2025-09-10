<?php

namespace App\Http\Controllers;

use App\Models\Usulan;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user?->role ?? 'guest';

        /**
         * =============== Periode (YYYY-MM) -> rentang tanggal =================
         */
        $period = $request->get('period', now()->format('Y-m'));
        try {
            [$yy, $mm] = array_map('intval', explode('-', $period));
            $start = now()->setDate($yy, $mm, 1)->startOfDay();
        } catch (\Throwable $e) {
            $start = now()->startOfMonth()->startOfDay();
            $period = $start->format('Y-m');
        }
        $end = (clone $start)->endOfMonth()->endOfDay();

        /**
         * =============== Deteksi ketersediaan kolom =================
         */
        $has = fn(string $col) => Schema::hasColumn('usulans', $col);

        $hasStatus  = $has('status');              // global status
        $hasCreated = $has('created_by');          // id pembuat (staf)
        $hasUnitId  = $has('unit_id');             // unit filter admin
        $hasHarga   = $has('harga_perkiraan');     // harga satuan perkiraan
        $hasTotal   = $has('total_perkiraan');     // total = jumlah * harga (bila ada)
        $hasKU      = $has('kepala_unit_status');  // approved|rejected|null
        $hasKT      = $has('katimker_status');
        $hasKB      = $has('kabid_status');

        /**
         * =============== Base query per periode =================
         */
        $base = Usulan::query()->whereBetween('created_at', [$start, $end]);

        // Filter per role
        if ($role === 'staf' && $hasCreated) {
            $base->where('created_by', $user->id);
        } elseif ($role === 'admin') {
            // Admin boleh filter unit
            if ($hasUnitId && $request->filled('unit_id')) {
                $base->where('unit_id', $request->integer('unit_id'));
            }
        } else {
            // kepala_unit / katimker / kabid: lihat semua (atau sesuaikan sesuai kebutuhanmu)
        }

        /**
         * =============== Kartu Ringkas =================
         * Disetujui: status='disetujui' ATAU kabid_status='approved'
         * Ditolak   : status='ditolak'  ATAU ada salah satu *_status='rejected'
         * Menunggu  : total - disetujui - ditolak
         */
        $total = (clone $base)->count();

        $approvedQ = (clone $base);
        $approvedQ->where(function ($q) use ($hasStatus, $hasKB) {
            if ($hasStatus) $q->orWhere('status', 'disetujui');
            if ($hasKB)     $q->orWhere('kabid_status', 'approved');
        });
        $disetujui = $approvedQ->count();

        $rejectedQ = (clone $base);
        $rejectedQ->where(function ($q) use ($hasStatus, $hasKU, $hasKT, $hasKB) {
            if ($hasStatus) $q->orWhere('status', 'ditolak');
            if ($hasKU)     $q->orWhere('kepala_unit_status', 'rejected');
            if ($hasKT)     $q->orWhere('katimker_status', 'rejected');
            if ($hasKB)     $q->orWhere('kabid_status', 'rejected');
        });
        $ditolak = $rejectedQ->count();

        $menunggu = max(0, $total - $disetujui - $ditolak);

        // Nilai total disetujui (prioritas pakai total_perkiraan jika ada)
        $nilaiQ = (clone $approvedQ); // sudah berisi kondisi approved adaptif
        if ($hasTotal) {
            $nilai_total_disetujui = (int) $nilaiQ->sum('total_perkiraan');
        } elseif ($hasHarga) {
            // fallback kasar: jumlah * harga_perkiraan
            $nilai_total_disetujui = (int) $nilaiQ
                ->selectRaw('COALESCE(SUM(COALESCE(jumlah,0) * COALESCE(harga_perkiraan,0)),0) as agg')
                ->value('agg');
        } else {
            $nilai_total_disetujui = 0;
        }

        $cards = [
            'total'                 => $total,
            'menunggu'              => $menunggu,
            'disetujui'             => $disetujui,
            'ditolak'               => $ditolak,
            'nilai_total_disetujui' => $nilai_total_disetujui,
        ];

        /**
         * =============== Usulan terbaru (untuk tabel) =================
         * Sertakan relasi lokasi & unit, plus status_tampil konsisten.
         */
        $recent = (clone $base)
            ->with(['unit','lantai','ruang','subRuang'])
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(function ($row) use ($hasStatus, $hasKU, $hasKT, $hasKB) {
                $row->status_tampil = $this->deriveDisplayStatus($row, $hasStatus, $hasKU, $hasKT, $hasKB);
                return $row;
            });

        // Data dropdown unit untuk admin (opsional)
        $units = [];
        if ($role === 'admin') {
            $units = Unit::orderBy('nama')->get(['id','nama']);
        }

        return view('home', [
            'role'   => $role,
            'period' => $period,
            'start'  => $start,
            'end'    => $end,
            'cards'  => $cards,
            'recent' => $recent,
            'units'  => $units,
        ]);
    }

    /**
     * Menentukan status tampilan yang konsisten untuk baris tabel:
     * - Final tertinggi: kabid_status (approved -> disetujui, rejected -> ditolak)
     * - Kalau belum final, cek penolakan di katimker/kepala (-> ditolak)
     * - Kalau ada status global final (disetujui/ditolak), ikuti itu
     * - Sisanya: menunggu
     */
    private function deriveDisplayStatus($row, bool $hasStatus, bool $hasKU, bool $hasKT, bool $hasKB): string
    {
        if ($hasKB && !empty($row->kabid_status)) {
            if ($row->kabid_status === 'approved') return 'disetujui';
            if ($row->kabid_status === 'rejected') return 'ditolak';
        }

        if ($hasKT && ($row->katimker_status ?? null) === 'rejected') return 'ditolak';
        if ($hasKU && ($row->kepala_unit_status ?? null) === 'rejected') return 'ditolak';

        if ($hasStatus && !empty($row->status)) {
            if ($row->status === 'disetujui') return 'disetujui';
            if ($row->status === 'ditolak')    return 'ditolak';
        }

        return 'menunggu';
    }
}
