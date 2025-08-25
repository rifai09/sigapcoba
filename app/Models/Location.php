<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    // opsional, tapi aman untuk eksplisitkan tabel
    protected $table = 'locations';

    protected $fillable = ['nama', 'level', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id');
    }
}
