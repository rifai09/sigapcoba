<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usulan extends Model
{
    use HasFactory;

    protected $table = 'usulans';

    protected $fillable = [
        'nama_barang',
        'spesifikasi',
        'keterangan',
        'gambar',
        'jumlah',
        'harga_perkiraan',
        'satuan',
        'unit_id',
        'lantai_id',
        'ruang_id',
        'sub_ruang_id',
        'status'
    ];

    public function unit()
    {
        return $this->belongsTo(\App\Models\Unit::class, 'unit_id');
    }

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
}
