<?php

namespace App\Http\Controllers;

use App\Models\Usulan;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Halaman daftar usulan yang disetujui final (Kabid),
     * dengan filter periode (YYYY-MM) + unit (opsional).
     * Memuat juga nama approver per level.
     */
    public function index(Request $request)
    {
        // ===== Periode =====
        $period = $request->get('period', now()->format('Y-m'));
        [$start, $end] = $this->resolvePeriod($period);

        // ===== Cek kolom yang tersedia (adaptif ke skema Anda) =====
        $has      = fn(string $col) => Schema::hasColumn('usulans', $col);
        $hasKB    = $has('kabid_status');
        $hasStat  = $has('status');
        $hasUnit  = $has('unit_id');
        $hasHarga = $has('harga_perkiraan');
        $hasTotal = $has('total_perkiraan');

        // ===== Query dasar: final approved =====
        $q = Usulan::query()
            ->whereBetween('created_at', [$start, $end])
            ->when($hasKB || $hasStat, function ($qb) use ($hasKB, $hasStat) {
                $qb->where(function ($w) use ($hasKB, $hasStat) {
                    if ($hasKB) {
                        $w->orWhere('kabid_status', 'approved');
                    }
                    if ($hasStat) {
                        $w->orWhere('status', 'disetujui');
                    }
                });
            })
            ->with([
                'unit',
                'lantai',
                'ruang',
                'subRuang',
                // nama approver per level
                'kepalaUnit:id,name',
                'katimker:id,name',
                'kabid:id,name',
            ])
            ->latest('id');

        // Filter Unit (opsional)
        if ($hasUnit && $request->filled('unit_id')) {
            $q->where('unit_id', (int) $request->input('unit_id'));
        }

        $rows = $q->paginate(20)->withQueryString();

        // Data Unit untuk dropdown
        $units = $hasUnit ? Unit::orderBy('nama')->get(['id', 'nama']) : collect();

        // Hitung nilai total (ringkas)
        $sumQuery = clone $q;
        if ($hasTotal) {
            $nilai_total = (int) $sumQuery->sum('total_perkiraan');
        } elseif ($hasHarga) {
            $nilai_total = (int) $sumQuery
                ->selectRaw('COALESCE(SUM(COALESCE(jumlah,0) * COALESCE(harga_perkiraan,0)),0) as agg')
                ->value('agg');
        } else {
            $nilai_total = 0;
        }

        return view('reports.approved', [
            'rows'        => $rows,
            'units'       => $units,
            'period'      => $period,
            'start'       => $start,
            'end'         => $end,
            'nilai_total' => $nilai_total,
        ]);
    }

    /**
     * Export ke "Excel" (CSV ramah-Excel):
     * - Delimiter ; (semicolon) agar tiap nilai jatuh ke kolom terpisah di Excel ID
     * - BOM UTF-8
     * - Baris pembuka "sep=;" untuk memberi tahu Excel delimiter yang dipakai
     * - Sertakan nama & waktu approve per level
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        [$start, $end] = $this->resolvePeriod($request->get('period', now()->format('Y-m')));

        $has      = fn(string $col) => Schema::hasColumn('usulans', $col);
        $hasKB    = $has('kabid_status');
        $hasStat  = $has('status');
        $hasUnit  = $has('unit_id');

        $q = Usulan::query()
            ->whereBetween('created_at', [$start, $end])
            ->when($hasKB || $hasStat, function ($qb) use ($hasKB, $hasStat) {
                $qb->where(function ($w) use ($hasKB, $hasStat) {
                    if ($hasKB) {
                        $w->orWhere('kabid_status', 'approved');
                    }
                    if ($hasStat) {
                        $w->orWhere('status', 'disetujui');
                    }
                });
            })
            ->with([
                'unit',
                'lantai',
                'ruang',
                'subRuang',
                'kepalaUnit:id,name',
                'katimker:id,name',
                'kabid:id,name',
            ])
            ->orderBy('id', 'desc');

        if ($hasUnit && $request->filled('unit_id')) {
            $q->where('unit_id', (int) $request->input('unit_id'));
        }

        $filename = 'laporan-usulan-disetujui-' . $start->format('Y_m') . '.csv';

        $callback = function () use ($q) {
            // BOM UTF-8 + hint delimiter
            echo "\xEF\xBB\xBF";
            echo "sep=;\r\n";

            $out = fopen('php://output', 'w');

            // Header
            fputcsv($out, [
                'No.',
                'Tanggal Usulan',
                'Unit',
                'Nama Barang/Jasa',
                'Jumlah',
                'Satuan',
                'Harga Perkiraan',
                'Total Perkiraan',
                'Detail Penempatan',
                'Kepala Unit (Nama)',
                'Kepala Unit (Waktu)',
                'Katimker (Nama)',
                'Katimker (Waktu)',
                'Kabid (Nama)',
                'Kabid (Waktu/Final)',
                'Status Final',
            ], ';');

            $no = 0;
            $q->chunk(500, function ($chunk) use ($out, &$no) {
                foreach ($chunk as $r) {
                    $no++;
                    $jumlah = (int) ($r->jumlah ?? 0);
                    $harga  = (int) ($r->harga_perkiraan ?? 0);
                    $total  = (int) ($r->total_perkiraan ?? ($jumlah * $harga));

                    $parts = array_filter([
                        $r->lantai->nama ?? null,
                        $r->ruang->nama ?? null,
                        (isset($r->subRuang->nama) && $r->subRuang->nama !== 'Tidak ada detail sub ruang') ? $r->subRuang->nama : null,
                    ]);

                    fputcsv($out, [
                        $no,
                        optional($r->created_at)->format('Y-m-d'),
                        $r->unit->nama ?? '-',
                        $r->nama_barang,
                        $jumlah,
                        $r->satuan ?? '',
                        $harga,
                        $total,
                        $parts ? implode(' - ', $parts) : '-',
                        optional($r->kepalaUnit)->name ?? '',
                        optional($r->kepala_unit_at)->format('Y-m-d H:i'),
                        optional($r->katimker)->name ?? '',
                        optional($r->katimker_at)->format('Y-m-d H:i'),
                        optional($r->kabid)->name ?? '',
                        optional($r->kabid_at)->format('Y-m-d H:i'),
                        'disetujui',
                    ], ';');
                }
            });

            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Export PDF (download attachment). Blade akan menyembunyikan tombol Back/Print.
     */
    public function exportPdf(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request->get('period', now()->format('Y-m')));

        $has      = fn(string $col) => Schema::hasColumn('usulans', $col);
        $hasKB    = $has('kabid_status');
        $hasStat  = $has('status');
        $hasUnit  = $has('unit_id');
        $hasHarga = $has('harga_perkiraan');
        $hasTotal = $has('total_perkiraan');

        $q = Usulan::query()
            ->whereBetween('created_at', [$start, $end])
            ->when($hasKB || $hasStat, function ($qb) use ($hasKB, $hasStat) {
                $qb->where(function ($w) use ($hasKB, $hasStat) {
                    if ($hasKB) {
                        $w->orWhere('kabid_status', 'approved');
                    }
                    if ($hasStat) {
                        $w->orWhere('status', 'disetujui');
                    }
                });
            })
            ->with([
                'unit',
                'lantai',
                'ruang',
                'subRuang',
                'kepalaUnit:id,name',
                'katimker:id,name',
                'kabid:id,name',
            ])
            ->orderBy('id', 'desc');

        if ($hasUnit && $request->filled('unit_id')) {
            $q->where('unit_id', (int) $request->input('unit_id'));
        }

        $rows = $q->get();

        // Hitung total untuk ringkasan
        $sumQuery = clone $q;
        if ($hasTotal) {
            $nilai_total = (int) $sumQuery->sum('total_perkiraan');
        } elseif ($hasHarga) {
            $nilai_total = (int) $sumQuery
                ->selectRaw('COALESCE(SUM(COALESCE(jumlah,0) * COALESCE(harga_perkiraan,0)),0) as agg')
                ->value('agg');
        } else {
            $nilai_total = 0;
        }

        $data = [
            'rows'        => $rows,
            'start'       => $start,
            'end'         => $end,
            'nilai_total' => $nilai_total,
            'title'       => 'Laporan Usulan Disetujui',
            'is_pdf'      => true, // <- agar tombol Back/Print tersembunyi
        ];

        if (app()->bound('dompdf.wrapper')) {
            $pdf = app('dompdf.wrapper');
            $pdf->set_option('isHtml5ParserEnabled', true);
            $pdf->set_option('isRemoteEnabled', true);
            $pdf->loadView('reports.approved_pdf', $data)->setPaper('a4', 'landscape');

            $filename = 'laporan-usulan-disetujui-' . $start->format('Y_m') . '.pdf';
            return $pdf->download($filename); // attachment
        }

        // Fallback HTML
        return view('reports.approved_pdf', $data);
    }

    /**
     * Preview PDF (inline/stream) — untuk pratinjau di browser.
     * Konten sama persis dengan exportPdf, bedanya stream (inline).
     */
    public function previewPdf(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request->get('period', now()->format('Y-m')));

        $has      = fn(string $col) => Schema::hasColumn('usulans', $col);
        $hasKB    = $has('kabid_status');
        $hasStat  = $has('status');
        $hasUnit  = $has('unit_id');
        $hasHarga = $has('harga_perkiraan');
        $hasTotal = $has('total_perkiraan');

        $q = Usulan::query()
            ->whereBetween('created_at', [$start, $end])
            ->when($hasKB || $hasStat, function ($qb) use ($hasKB, $hasStat) {
                $qb->where(function ($w) use ($hasKB, $hasStat) {
                    if ($hasKB) {
                        $w->orWhere('kabid_status', 'approved');
                    }
                    if ($hasStat) {
                        $w->orWhere('status', 'disetujui');
                    }
                });
            })
            ->with([
                'unit',
                'lantai',
                'ruang',
                'subRuang',
                'kepalaUnit:id,name',
                'katimker:id,name',
                'kabid:id,name',
            ])
            ->orderBy('id', 'desc');

        if ($hasUnit && $request->filled('unit_id')) {
            $q->where('unit_id', (int) $request->input('unit_id'));
        }

        $rows = $q->get();

        $sumQuery = clone $q;
        if ($hasTotal) {
            $nilai_total = (int) $sumQuery->sum('total_perkiraan');
        } elseif ($hasHarga) {
            $nilai_total = (int) $sumQuery
                ->selectRaw('COALESCE(SUM(COALESCE(jumlah,0) * COALESCE(harga_perkiraan,0)),0) as agg')
                ->value('agg');
        } else {
            $nilai_total = 0;
        }

        $data = [
            'rows'        => $rows,
            'start'       => $start,
            'end'         => $end,
            'nilai_total' => $nilai_total,
            'title'       => 'Laporan Usulan Disetujui',
            'is_pdf'      => true, // hide tombol Back/Print di blade saat render oleh Dompdf
        ];

        if (app()->bound('dompdf.wrapper')) {
            $pdf = app('dompdf.wrapper');
            $pdf->set_option('isHtml5ParserEnabled', true);
            $pdf->set_option('isRemoteEnabled', true);
            $pdf->loadView('reports.approved_pdf', $data)->setPaper('a4', 'landscape');

            $filename = 'laporan-usulan-disetujui-' . $start->format('Y_m') . '.pdf';
            return $pdf->stream($filename); // INLINE preview
        }

        // Fallback HTML (tanpa tombol Back/Print karena is_pdf = true)
        return view('reports.approved_pdf', $data);
    }


    // ===== Helpers =====

    private function resolvePeriod(string $period): array
    {
        try {
            [$yy, $mm] = array_map('intval', explode('-', $period));
            $start = now()->setDate($yy, $mm, 1)->startOfDay();
        } catch (\Throwable $e) {
            $start = now()->startOfMonth()->startOfDay();
        }
        $end = (clone $start)->endOfMonth()->endOfDay();
        return [$start, $end];
    }
}
