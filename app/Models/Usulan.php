<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usulan extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_barang',
        'spesifikasi',
        'keterangan',
        'gambar',
        'jumlah',
        'satuan',
        'harga_pagu',
        'perkiraan_harga',
        'penjual_1',
        'harga_penjual_1',
        'link_penjual_1',
        'penjual_2',
        'harga_penjual_2',
        'link_penjual_2',
        'penjual_3',
        'harga_penjual_3',
        'link_penjual_3',
        'status',
    ];
}
