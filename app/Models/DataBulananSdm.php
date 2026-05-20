<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataBulananSdm extends Model
{
    protected $table = 'data_bulanan_sdms';
    protected $guarded = ['id']; // Mass assignment

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}