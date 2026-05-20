<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataInformasiIntelejen extends Model
{
    protected $table = 'data_informasi_intelejens';
    protected $guarded = ['id']; // Mass assignment untuk semua kolom

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}