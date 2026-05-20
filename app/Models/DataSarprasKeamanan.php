<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSarprasKeamanan extends Model
{
    protected $table = 'data_sarpras_keamanans';
    protected $guarded = ['id']; // Mempermudah mass assignment

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}