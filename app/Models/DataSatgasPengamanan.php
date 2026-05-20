<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSatgasPengamanan extends Model
{
    protected $table = 'data_satgas_pengamanans';
    protected $guarded = ['id']; // Mass assignment lebih mudah

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}