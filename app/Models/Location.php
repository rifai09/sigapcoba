<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    protected $table = 'locations'; // Pastikan nama tabel sesuai dengan yang ada di database
    use HasFactory;

    protected $fillable = ['nama', 'level', 'parent_id'];

    // Relasi: Lokasi memiliki banyak anak (ruang atau sub ruang)
    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    // Relasi: Lokasi bisa punya induk
    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }
}
