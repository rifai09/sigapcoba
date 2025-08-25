<?php

namespace App\Http\Controllers;

use App\Http\Requests\KepalaUnitApproveRequest;
use App\Http\Requests\KepalaUnitRejectRequest;
use App\Http\Requests\KatimkerApproveRequest;
use App\Http\Requests\KatimkerRejectRequest;
use App\Http\Requests\KabidApproveRequest;
use App\Http\Requests\KabidRejectRequest;
use App\Models\Usulan;
use App\Models\ApprovalLog;
use App\Models\User;
use App\Notifications\UsulanNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PersetujuanController extends Controller
{
    /**
     * Daftar usulan per role + tab DataTables (waiting|approved|rejected).
     */
    public function index(Request $request)
    {
        $role = Auth::user()->role;               // 'kepala_unit' | 'katimker' | 'kabid' | 'admin'
        $tab  = $request->get('tab', 'waiting');  // default 'waiting'

        if ($request->ajax()) {
            $q = Usulan::query()
                ->with(['unit','lantai','ruang','subRuang'])
                ->latest('id');

            if ($role === 'kepala_unit') {
                if ($tab === 'waiting') {
                    $q->where('status', 'menunggu_kepala_unit');
                } elseif ($tab === 'approved') {
                    $q->whereHas('approvalLogs', function ($s) {
                        $s->where('role', ApprovalLog::ROLE_KEPALA_UNIT)
                          ->where('status', ApprovalLog::STATUS_DISETUJUI)
                          ->where('user_id', Auth::id());
                    });
                } elseif ($tab === 'rejected') {
                    $q->whereHas('approvalLogs', function ($s) {
                        $s->where('role', ApprovalLog::ROLE_KEPALA_UNIT)
                          ->where('status', ApprovalLog::STATUS_DITOLAK)
                          ->where('user_id', Auth::id());
                    });
                }
            } elseif ($role === 'katimker') {
                if ($tab === 'waiting') {
                    $q->where('status', 'menunggu_katimker');
                } elseif ($tab === 'approved') {
                    $q->where('katimker_status', 'approved')
                      ->where('katimker_by', Auth::id());
                } elseif ($tab === 'rejected') {
                    $q->where('katimker_status', 'rejected')
                      ->where('katimker_by', Auth::id());
                }
            } elseif ($role === 'kabid') {
                if ($tab === 'waiting') {
                    $q->where('status', 'menunggu_kabid');
                } elseif ($tab === 'approved') {
                    $q->where('kabid_status', 'approved')
                      ->where('kabid_by', Auth::id());
                } elseif ($tab === 'rejected') {
                    $q->where('kabid_status', 'rejected')
                      ->where('kabid_by', Auth::id());
                }
            } else { // admin
                if ($tab === 'waiting') {
                    $q->whereIn('status', ['menunggu_kepala_unit','menunggu_katimker','menunggu_kabid']);
                } elseif ($tab === 'approved') {
                    $q->where('status', 'disetujui');
                } elseif ($tab === 'rejected') {
                    $q->where('status', 'ditolak');
                }
            }

            return DataTables::of($q)
                ->addColumn('nama_barang', fn ($r) => $r->nama_barang)
                ->addColumn('jumlah', fn ($r) => number_format((int)$r->jumlah))
                ->addColumn('satuan', fn ($r) => $r->satuan ?? '-')
                ->addColumn('harga_perkiraan', function ($r) {
                    return is_null($r->harga_perkiraan) ? '-' : number_format((int)$r->harga_perkiraan);
                })
                ->addColumn('total_perkiraan', function ($r) {
                    return is_null($r->total_perkiraan) ? '-' : number_format((int)$r->total_perkiraan);
                })
                ->addColumn('status', fn ($r) => $r->status)
                ->addColumn('aksi', function ($r) use ($role) {
                    return view('persetujuan._action', ['row' => $r, 'role' => $role])->render();
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        return view('persetujuan.index', compact('role', 'tab'));
    }

    /**
     * (Opsional) Detail terpisah—tidak wajib jika pakai modal pada index.blade.php
     */
    public function show(Request $request, Usulan $usulan)
    {
        $usulan->load(['approvalLogs.user','unit','lantai','ruang','subRuang']);
        $role = Auth::user()->role;
        return view('persetujuan.show', compact('usulan','role'));
    }

    /**
     * JSON histori persetujuan untuk modal (Step 8).
     * Return: role, status, prioritas, catatan, created_at, user_name
     */
    public function getLogs(Usulan $id)
    {
        $logs = ApprovalLog::with('user:id,name')
            ->where('usulan_id', $id->id)
            ->orderBy('created_at','asc')
            ->get()
            ->map(function($l){
                return [
                    'role'       => $l->role,
                    'status'     => $l->status,
                    'prioritas'  => $l->prioritas,
                    'catatan'    => $l->catatan,
                    'created_at' => $l->created_at,
                    'user_name'  => optional($l->user)->name,
                ];
            });

        return response()->json($logs);
    }

    /* ====================== KEPALA UNIT ====================== */

    public function kepalaUnitSetujui(KepalaUnitApproveRequest $request, Usulan $id)
    {
        $this->mustRole('kepala_unit');
        $data = $request->validated();

        if ($id->status !== 'menunggu_kepala_unit') {
            return back()->with('error', 'Usulan tidak dalam antrian Kepala Unit.');
        }

        DB::transaction(function () use ($id, $data) {
            // Audit log
            ApprovalLog::create([
                'usulan_id' => $id->id,
                'user_id'   => Auth::id(),
                'role'      => ApprovalLog::ROLE_KEPALA_UNIT,
                'status'    => ApprovalLog::STATUS_DISETUJUI,
                'prioritas' => $data['prioritas'] ?? null,
                'catatan'   => $data['catatan'] ?? null,
            ]);

            // Ringkasan simetris + lanjut ke Katimker
            $id->kepala_unit_status   = 'approved';
            $id->kepala_unit_priority = $data['prioritas'] ?? $id->kepala_unit_priority;
            $id->kepala_unit_note     = $data['catatan'] ?? $id->kepala_unit_note;
            $id->kepala_unit_by       = Auth::id();
            $id->kepala_unit_at       = now();
            $id->status               = 'menunggu_katimker';
            $id->save();
        });

        // Notifikasi ke semua Katimker
        $this->notifyRole(
            role: 'katimker',
            usulan: $id,
            title: 'Butuh persetujuan Katimker',
            message: 'Usulan "'.$id->nama_barang.'" menunggu persetujuan Katimker.',
            statusBaru: 'menunggu_katimker'
        );

        return back()->with('success', 'Usulan disetujui Kepala Unit & dikirim ke Katimker.');
    }

    public function kepalaUnitTolak(KepalaUnitRejectRequest $request, Usulan $id)
    {
        $this->mustRole('kepala_unit');
        $data = $request->validated();

        if (!in_array($id->status, ['menunggu_kepala_unit','menunggu_katimker','menunggu_kabid'], true)) {
            return back()->with('error', 'Status usulan tidak valid untuk ditolak Kepala Unit.');
        }

        DB::transaction(function () use ($id, $data) {
            // audit
            ApprovalLog::create([
                'usulan_id' => $id->id,
                'user_id'   => Auth::id(),
                'role'      => ApprovalLog::ROLE_KEPALA_UNIT,
                'status'    => ApprovalLog::STATUS_DITOLAK,
                'prioritas' => null,
                'catatan'   => $data['catatan'],
            ]);

            // ringkasan + final ditolak
            $id->kepala_unit_status = 'rejected';
            $id->kepala_unit_note   = $data['catatan'];
            $id->kepala_unit_by     = Auth::id();
            $id->kepala_unit_at     = now();
            $id->status             = 'ditolak';
            $id->save();
        });

        // Notifikasi ke staf pengusul (opsional, hanya jika ada kolom created_by)
        if (isset($id->created_by) && $id->created_by) {
            $this->notifyUserId(
                userId: $id->created_by,
                usulan: $id,
                title: 'Usulan ditolak Kepala Unit',
                message: 'Usulan "'.$id->nama_barang.'" ditolak oleh Kepala Unit.',
                statusBaru: 'ditolak'
            );
        }

        return back()->with('success', 'Usulan ditolak oleh Kepala Unit.');
    }

    public function kepalaUnitPrioritas(Request $request, Usulan $id)
    {
        $this->mustRole('kepala_unit');
        $data = $request->validate([
            'prioritas' => 'required|in:'.ApprovalLog::PRIORITAS_URGENT.','.ApprovalLog::PRIORITAS_NORMAL,
        ]);

        DB::transaction(function () use ($id, $data) {
            ApprovalLog::create([
                'usulan_id' => $id->id,
                'user_id'   => Auth::id(),
                'role'      => ApprovalLog::ROLE_KEPALA_UNIT,
                'status'    => ApprovalLog::STATUS_DISETUJUI,
                'prioritas' => $data['prioritas'],
                'catatan'   => 'Set prioritas Kepala Unit: '.$data['prioritas'],
            ]);

            // ringkasan (tidak mengubah status global)
            $id->kepala_unit_priority = $data['prioritas'];
            $id->kepala_unit_by       = $id->kepala_unit_by ?: Auth::id();
            $id->kepala_unit_at       = $id->kepala_unit_at ?: now();
            $id->save();
        });

        return back()->with('success', 'Prioritas dari Kepala Unit tersimpan.');
    }

    /* ====================== KATIMKER ====================== */

    public function katimkerSetujui(KatimkerApproveRequest $request, Usulan $id)
    {
        $this->mustRole('katimker');
        $v = $request->validated();

        if ($id->status !== 'menunggu_katimker') {
            return back()->with('error', 'Usulan tidak dalam antrian Katimker.');
        }

        DB::transaction(function () use ($id, $v) {
            // legacy summary (simetris)
            $id->katimker_status   = 'approved';
            $id->katimker_priority = $v['katimker_priority'];
            $id->katimker_by       = Auth::id();
            $id->katimker_at       = now();
            $id->status            = 'menunggu_kabid';

            if (!is_null($v['harga_perkiraan'] ?? null)) {
                $id->harga_perkiraan = (int) $v['harga_perkiraan'];
                $id->total_perkiraan = (int) $id->jumlah * (int) $v['harga_perkiraan'];
            }
            $id->save();

            // audit log
            ApprovalLog::create([
                'usulan_id' => $id->id,
                'user_id'   => Auth::id(),
                'role'      => ApprovalLog::ROLE_KATIMKER,
                'status'    => ApprovalLog::STATUS_DISETUJUI,
                'prioritas' => $v['katimker_priority'],
                'catatan'   => $v['catatan'] ?? null,
            ]);
        });

        // Notifikasi ke Kabid
        $this->notifyRole(
            role: 'kabid',
            usulan: $id,
            title: 'Butuh persetujuan Kabid (final)',
            message: 'Usulan "'.$id->nama_barang.'" menunggu persetujuan Kabid.',
            statusBaru: 'menunggu_kabid'
        );

        return back()->with('success', 'Usulan disetujui Katimker & dikirim ke Kabid.');
    }

    public function katimkerTolak(KatimkerRejectRequest $request, Usulan $id)
    {
        $this->mustRole('katimker');
        $v = $request->validated();

        if (!in_array($id->status, ['menunggu_katimker','menunggu_kabid'], true)) {
            return back()->with('error', 'Status usulan tidak valid untuk ditolak Katimker.');
        }

        DB::transaction(function () use ($id, $v) {
            // legacy
            $id->katimker_status = 'rejected';
            $id->katimker_note   = $v['katimker_note'];
            $id->katimker_by     = Auth::id();
            $id->katimker_at     = now();
            $id->status          = 'ditolak';
            $id->save();

            // log
            ApprovalLog::create([
                'usulan_id' => $id->id,
                'user_id'   => Auth::id(),
                'role'      => ApprovalLog::ROLE_KATIMKER,
                'status'    => ApprovalLog::STATUS_DITOLAK,
                'prioritas' => null,
                'catatan'   => $v['katimker_note'],
            ]);
        });

        // Notifikasi ke staf pengusul (opsional)
        if (isset($id->created_by) && $id->created_by) {
            $this->notifyUserId(
                userId: $id->created_by,
                usulan: $id,
                title: 'Usulan ditolak Katimker',
                message: 'Usulan "'.$id->nama_barang.'" ditolak oleh Katimker.',
                statusBaru: 'ditolak'
            );
        }

        return back()->with('success', 'Usulan ditolak oleh Katimker.');
    }

    public function katimkerPrioritas(Request $request, Usulan $id)
    {
        $this->mustRole('katimker');
        $data = $request->validate([
            'prioritas' => 'required|in:'.ApprovalLog::PRIORITAS_URGENT.','.ApprovalLog::PRIORITAS_NORMAL,
        ]);

        DB::transaction(function () use ($id, $data) {
            ApprovalLog::create([
                'usulan_id' => $id->id,
                'user_id'   => Auth::id(),
                'role'      => ApprovalLog::ROLE_KATIMKER,
                'status'    => ApprovalLog::STATUS_DISETUJUI,
                'prioritas' => $data['prioritas'],
                'catatan'   => 'Set prioritas Katimker: '.$data['prioritas'],
            ]);

            // legacy ringkasan prioritas
            $id->katimker_priority = $data['prioritas'];
            $id->katimker_by       = $id->katimker_by ?: Auth::id();
            $id->katimker_at       = $id->katimker_at ?: now();
            $id->save();
        });

        return back()->with('success', 'Prioritas dari Katimker tersimpan.');
    }

    /* ====================== KABID (FINAL) ====================== */

    public function kabidSetujui(KabidApproveRequest $request, Usulan $id)
    {
        $this->mustRole('kabid');
        $v = $request->validated();

        if ($id->status !== 'menunggu_kabid') {
            return back()->with('error', 'Usulan tidak dalam antrian Kabid.');
        }

        DB::transaction(function () use ($id, $v) {
            // legacy final
            $id->kabid_status    = 'approved';
            $id->kabid_priority  = $v['kabid_priority'];
            $id->kabid_by        = Auth::id();
            $id->kabid_at        = now();
            $id->status          = 'disetujui';
            $id->prioritas_final = $v['kabid_priority'];

            if (!is_null($v['harga_perkiraan'] ?? null)) {
                $id->harga_perkiraan = (int) $v['harga_perkiraan'];
                $id->total_perkiraan = (int) $id->jumlah * (int) $v['harga_perkiraan'];
            }
            $id->save();

            // audit log final
            ApprovalLog::create([
                'usulan_id' => $id->id,
                'user_id'   => Auth::id(),
                'role'      => ApprovalLog::ROLE_KABID,
                'status'    => ApprovalLog::STATUS_DISETUJUI,
                'prioritas' => $v['kabid_priority'],
                'catatan'   => $v['catatan'] ?? null,
            ]);
        });

        // Notifikasi ke staf pengusul (opsional)
        if (isset($id->created_by) && $id->created_by) {
            $this->notifyUserId(
                userId: $id->created_by,
                usulan: $id,
                title: 'Usulan disetujui (final)',
                message: 'Usulan "'.$id->nama_barang.'" telah disetujui final oleh Kabid.',
                statusBaru: 'disetujui'
            );
        }

        return back()->with('success', 'Usulan disetujui final oleh Kabid.');
    }

    public function kabidTolak(KabidRejectRequest $request, Usulan $id)
    {
        $this->mustRole('kabid');
        $v = $request->validated();

        if ($id->status !== 'menunggu_kabid') {
            return back()->with('error', 'Usulan tidak dalam antrian Kabid.');
        }

        DB::transaction(function () use ($id, $v) {
            // legacy final
            $id->kabid_status = 'rejected';
            $id->kabid_note   = $v['kabid_note'];
            $id->kabid_by     = Auth::id();
            $id->kabid_at     = now();
            $id->status       = 'ditolak';
            $id->save();

            // audit log final
            ApprovalLog::create([
                'usulan_id' => $id->id,
                'user_id'   => Auth::id(),
                'role'      => ApprovalLog::ROLE_KABID,
                'status'    => ApprovalLog::STATUS_DITOLAK,
                'prioritas' => null,
                'catatan'   => $v['kabid_note'],
            ]);
        });

        // Notifikasi ke staf pengusul (opsional)
        if (isset($id->created_by) && $id->created_by) {
            $this->notifyUserId(
                userId: $id->created_by,
                usulan: $id,
                title: 'Usulan ditolak (final)',
                message: 'Usulan "'.$id->nama_barang.'" ditolak final oleh Kabid.',
                statusBaru: 'ditolak'
            );
        }

        return back()->with('success', 'Usulan ditolak oleh Kabid.');
    }

    public function kabidPrioritas(Request $request, Usulan $id)
    {
        $this->mustRole('kabid');
        $data = $request->validate([
            'prioritas' => 'required|in:'.ApprovalLog::PRIORITAS_URGENT.','.ApprovalLog::PRIORITAS_NORMAL,
        ]);

        DB::transaction(function () use ($id, $data) {
            ApprovalLog::create([
                'usulan_id' => $id->id,
                'user_id'   => Auth::id(),
                'role'      => ApprovalLog::ROLE_KABID,
                'status'    => ApprovalLog::STATUS_DISETUJUI,
                'prioritas' => $data['prioritas'],
                'catatan'   => 'Set prioritas Kabid (final): '.$data['prioritas'],
            ]);

            // legacy ringkasan
            $id->kabid_priority  = $data['prioritas'];
            $id->prioritas_final = $data['prioritas'];
            $id->kabid_by        = $id->kabid_by ?: Auth::id();
            $id->kabid_at        = $id->kabid_at ?: now();
            $id->save();
        });

        return back()->with('success', 'Prioritas final Kabid tersimpan.');
    }

    /* ====================== HELPERS ====================== */

    private function mustRole(string $role): void
    {
        $userRole = Auth::user()->role;
        if (!in_array($userRole, [$role, 'admin'], true)) {
            abort(403, 'Akses ditolak.');
        }
    }

    /**
     * Kirim notifikasi ke semua user dengan role tertentu.
     */
    private function notifyRole(string $role, Usulan $usulan, string $title, string $message, string $statusBaru, ?string $url = null): void
    {
        $users = User::where('role', $role)->get();
        foreach ($users as $u) {
            $u->notify(new UsulanNotification(
                usulanId:   $usulan->id,
                judul:      $title,
                pesan:      $message,
                statusBaru: $statusBaru,
                byUserId:   Auth::id(),
                byUserName: Auth::user()->name,
                url:        $url ?? route('persetujuan.index', ['tab' => 'waiting'])
            ));
        }
    }

    /**
     * Kirim notifikasi ke user tertentu (by ID). Aman dipanggil hanya jika user ada.
     */
    private function notifyUserId(int $userId, Usulan $usulan, string $title, string $message, string $statusBaru, ?string $url = null): void
    {
        $u = User::find($userId);
        if (!$u) return;

        $u->notify(new UsulanNotification(
            usulanId:   $usulan->id,
            judul:      $title,
            pesan:      $message,
            statusBaru: $statusBaru,
            byUserId:   Auth::id(),
            byUserName: Auth::user()->name,
            url:        $url ?? route('persetujuan.index', ['tab' => 'waiting'])
        ));
    }
}
