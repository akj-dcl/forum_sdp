<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPublikasiSdm extends Model
{
    protected $table = 'data_publikasi_sdms';
    protected $guarded = ['id']; // Mass assignment

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}