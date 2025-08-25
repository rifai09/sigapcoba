<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Unit;
use App\Models\Location;
use App\Models\User;
use App\Models\ApprovalLog;

class Usulan extends Model
{
    use HasFactory;

    protected $table = 'usulans';

    /**
     * NOTE:
     * - Hybrid: ApprovalLog = sumber kebenaran audit; kolom ringkasan per level dipakai untuk query cepat & kompatibilitas UI.
     * - Kini ditambah ringkasan untuk KEPALA UNIT agar simetris dengan Katimker & Kabid.
     */
    protected $fillable = [
        // Input staf
        'nama_barang','spesifikasi','alasan_pengusulan','keterangan','gambar',

        // Angka & perhitungan
        'jumlah','satuan','persediaan_saat_ini',
        'harga_perkiraan','total_perkiraan',

        // Lokasi & unit
        'unit_id','created_by', // <— DITAMBAH created_by
        'lantai_id','ruang_id','sub_ruang_id',

        // Status global alur
        'status',

        // ===== Ringkasan KEPALA UNIT (baru, simetris) =====
        'kepala_unit_status','kepala_unit_priority','kepala_unit_note','kepala_unit_by','kepala_unit_at',

        // ===== Ringkasan KATIMKER (legacy/kompatibel) =====
        'katimker_status','katimker_priority','katimker_note','katimker_by','katimker_at',

        // ===== Ringkasan KABID (legacy/kompatibel) =====
        'kabid_status','kabid_priority','kabid_note','kabid_by','kabid_at',

        // Final
        'prioritas_final',
    ];

    protected $casts = [
        'jumlah'               => 'integer',
        'persediaan_saat_ini'  => 'integer',
        'harga_perkiraan'      => 'integer',
        'total_perkiraan'      => 'integer',

        'kepala_unit_at'       => 'datetime',
        'katimker_at'          => 'datetime',
        'kabid_at'             => 'datetime',
    ];

    /* ===================== RELATIONS ===================== */

    // Unit pengusul
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    // Staf pembuat usulan
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Lokasi: lantai, ruang, sub-ruang (semua ke model Location yang sama)
    public function lantai()
    {
        return $this->belongsTo(Location::class, 'lantai_id');
    }

    public function ruang()
    {
        return $this->belongsTo(Location::class, 'ruang_id');
    }

    public function subRuang()
    {
        return $this->belongsTo(Location::class, 'sub_ruang_id');
    }

    // alias kalau ada yang memanggil sub_ruang()
    public function sub_ruang()
    {
        return $this->subRuang();
    }

    // Jejak approver (ringkasan per level)
    public function kepalaUnit()
    {
        return $this->belongsTo(User::class, 'kepala_unit_by');
    }

    public function katimker()
    {
        return $this->belongsTo(User::class, 'katimker_by');
    }

    public function kabid()
    {
        return $this->belongsTo(User::class, 'kabid_by');
    }

    // Histori persetujuan (audit utama)
    public function approvalLogs()
    {
        return $this->hasMany(ApprovalLog::class);
    }

    /* ===================== HELPERS ===================== */

    // Log terbaru per role
    public function latestApprovalByRole(string $role)
    {
        return $this->approvalLogs()
            ->where('role', $role)
            ->latest('id')
            ->first();
    }

    // Status keputusan per level (null bila belum ada)
    public function keputusanKepalaUnit(): ?string
    {
        return optional($this->latestApprovalByRole(ApprovalLog::ROLE_KEPALA_UNIT))->status;
    }

    public function keputusanKatimker(): ?string
    {
        return optional($this->latestApprovalByRole(ApprovalLog::ROLE_KATIMKER))->status;
    }

    public function keputusanKabid(): ?string
    {
        return optional($this->latestApprovalByRole(ApprovalLog::ROLE_KABID))->status;
    }

    // Prioritas terbaru per level (urgent|normal|null)
    public function latestPriorityByRole(string $role): ?string
    {
        return optional(
            $this->approvalLogs()
                ->where('role', $role)
                ->whereIn('prioritas', [ApprovalLog::PRIORITAS_URGENT, ApprovalLog::PRIORITAS_NORMAL])
                ->latest('id')
                ->first()
        )->prioritas;
    }

    public function prioritasKepalaUnit(): ?string
    {
        return $this->latestPriorityByRole(ApprovalLog::ROLE_KEPALA_UNIT);
    }

    public function prioritasKatimker(): ?string
    {
        return $this->latestPriorityByRole(ApprovalLog::ROLE_KATIMKER);
    }

    public function prioritasKabid(): ?string
    {
        return $this->latestPriorityByRole(ApprovalLog::ROLE_KABID);
    }

    /**
     * Prioritas efektif (dipakai UI/rekap):
     * 1) Kabid jika ada
     * 2) jika belum: Katimker
     * 3) jika belum: Kepala Unit
     * 4) jika belum semua: null
     */
    public function prioritasEfektif(): ?string
    {
        return $this->prioritasKabid()
            ?? $this->prioritasKatimker()
            ?? $this->prioritasKepalaUnit()
            ?? null;
    }

    /**
     * Ringkas status agregat untuk listing
     * Urutan evaluasi: Kabid → Katimker → Kepala Unit → default
     */
    public function ringkasStatusPersetujuan(): string
    {
        $kepala = $this->keputusanKepalaUnit();
        $katim  = $this->keputusanKatimker();
        $kabid  = $this->keputusanKabid();

        if ($kabid === ApprovalLog::STATUS_DITOLAK)   return 'Ditolak Kabid';
        if ($kabid === ApprovalLog::STATUS_DISETUJUI) return 'Disetujui Kabid';

        if ($katim === ApprovalLog::STATUS_DITOLAK)   return 'Ditolak Katimker';
        if ($katim === ApprovalLog::STATUS_DISETUJUI) return 'Menunggu Kabid';

        if ($kepala === ApprovalLog::STATUS_DITOLAK)  return 'Ditolak Kepala Unit';
        if ($kepala === ApprovalLog::STATUS_DISETUJUI) return 'Menunggu Katimker';

        return 'Menunggu Kepala Unit';
    }

    /* ===================== (Opsional) SCOPES ===================== */

    public function scopeWaitingForKepalaUnit($q)
    {
        return $q->where('status', 'menunggu_kepala_unit');
    }

    public function scopeWaitingForKatimker($q)
    {
        return $q->where('status', 'menunggu_katimker');
    }

    public function scopeWaitingForKabid($q)
    {
        return $q->where('status', 'menunggu_kabid');
    }

    public function scopeApproved($q)
    {
        return $q->where('status', 'disetujui');
    }

    public function scopeRejected($q)
    {
        return $q->where('status', 'ditolak');
    }
}
