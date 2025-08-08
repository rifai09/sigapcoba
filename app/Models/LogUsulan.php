<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogUsulan extends Model
{
    protected $table = 'log_usulan';

    protected $fillable = [
        'usulan_id',
        'user_id',
        'status',
        'keterangan',
        'urgensi',
        'created_at',
    ];

    public $timestamps = false;

    // Relasi ke tabel Usulan
    public function usulan()
    {
        return $this->belongsTo(Usulan::class, 'usulan_id');
    }

    // Relasi ke tabel User (jika ada)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
