<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lokasi extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'level', 'parent_id'];

    // Relasi: Lokasi memiliki banyak anak (ruang atau sub ruang)
    public function children()
    {
        return $this->hasMany(Lokasi::class, 'parent_id');
    }

    // Relasi: Lokasi bisa punya induk
    public function parent()
    {
        return $this->belongsTo(Lokasi::class, 'parent_id');
    }
}
