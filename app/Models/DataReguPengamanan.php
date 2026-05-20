<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataReguPengamanan extends Model
{
    protected $table = 'data_regu_pengamanans';
    protected $guarded = ['id']; // Mempermudah agar semua kolom bisa diisi massal

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}