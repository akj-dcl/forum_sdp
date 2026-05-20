<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataRealisasiAnggaran extends Model
{
    protected $table = 'data_realisasi_anggarans';
    protected $guarded = ['id'];

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}