<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPelanggaranTatib extends Model
{
    protected $table = 'data_pelanggaran_tatibs';
    protected $guarded = ['id']; // Mass assignment

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}