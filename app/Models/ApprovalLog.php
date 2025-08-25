<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalLog extends Model
{
    use HasFactory;

    protected $table = 'approval_logs';

    protected $fillable = [
        'usulan_id',
        'user_id',
        'role',       // kepala_unit | katimker | kabid
        'status',     // disetujui | ditolak
        'catatan',
        'prioritas',  // urgent | normal | null
    ];

    /** KONSTANTA */
    public const ROLE_KEPALA_UNIT = 'kepala_unit';
    public const ROLE_KATIMKER    = 'katimker';
    public const ROLE_KABID       = 'kabid';

    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK   = 'ditolak';

    public const PRIORITAS_URGENT = 'urgent';
    public const PRIORITAS_NORMAL = 'normal';

    /** RELASI */
    public function usulan()
    {
        return $this->belongsTo(Usulan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** SCOPE */
    public function scopeByRole($q, string $role)
    {
        return $q->where('role', $role);
    }

    public function scopeApproved($q)
    {
        return $q->where('status', self::STATUS_DISETUJUI);
    }

    public function scopeRejected($q)
    {
        return $q->where('status', self::STATUS_DITOLAK);
    }

    /** CEK apakah ada prioritas */
    public function hasPriority(): bool
    {
        return in_array($this->prioritas, [self::PRIORITAS_URGENT, self::PRIORITAS_NORMAL], true);
    }
}
