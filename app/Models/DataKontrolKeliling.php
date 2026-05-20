<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataKontrolKeliling extends Model
{
    protected $table = 'data_kontrol_kelilings';

    // Menggunakan guarded agar kode lebih ringkas
    protected $guarded = ['id'];

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}