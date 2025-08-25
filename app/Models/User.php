<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** ATTRIBUTES */
    protected $fillable = [
        'name',
        'email',
        'password',
        // 'role', // <-- hanya aktifkan jika memang Anda punya kolom role di tabel users
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /** RELATIONS */
    public function approvalLogs()
    {
        return $this->hasMany(\App\Models\ApprovalLog::class);
    }

    // (Opsional) Helper sederhana jika Anda punya kolom 'role' di tabel users
    // public function isKepalaUnit(): bool { return $this->role === 'kepala_unit'; }
    // public function isKatimker(): bool    { return $this->role === 'katimker'; }
    // public function isKabid(): bool       { return $this->role === 'kabid'; }
}
