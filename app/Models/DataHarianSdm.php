<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataHarianSdm extends Model
{
    protected $table = 'data_harian_sdms';
    protected $guarded = ['id'];

    protected $casts = [
        'kehadiran_pegawai' => 'array',
    ];

    public function upt() {
        return $this->belongsTo(Upt::class);
    }
}